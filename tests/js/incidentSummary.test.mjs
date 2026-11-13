import test from 'node:test';
import assert from 'node:assert/strict';
import {readFile} from 'node:fs/promises';
import {parse, compileScript, compileTemplate} from '@vue/compiler-sfc';
import {createRenderer, h, reactive, nextTick, markRaw} from 'vue';

// Exercise the real SFC render/setup using Vue's host-independent renderer.
const source = await readFile(new URL('../../resources/js/Pages/Incidents/Partials/UKIOSummary.vue', import.meta.url), 'utf8');
const {descriptor} = parse(source);
let compiled = compileScript(descriptor, {id: 'summary-test', inlineTemplate: true}).content;
compiled = compiled.replace(/from ['"]vue['"]/g, `from '${import.meta.resolve('vue')}'`)
    .replace(/import Block from [^;]+;/, "const Block = {setup: (_, {slots}) => () => slots.default?.()};")
    .replace(/import TextInput from [^;]+;/, "const TextInput = 'test-input';");
const Summary = (await import('data:text/javascript;base64,' + Buffer.from(compiled).toString('base64'))).default;
const node = (type, text = '') => ({type, text, props: {}, children: [], parent: null});
const renderer = createRenderer({
    createElement: node, createText: text => node('#text', text), createComment: text => node('#comment', text),
    setText: (n, text) => n.text = text, setElementText: (n, text) => n.text = text,
    parentNode: n => n.parent, nextSibling: n => n.parent?.children[n.parent.children.indexOf(n) + 1],
    patchProp: (n, key, old, value) => n.props[key] = value,
    insert(n, parent, anchor) { if (n.parent) n.parent.children.splice(n.parent.children.indexOf(n), 1); n.parent = parent; const i = parent.children.indexOf(anchor); i < 0 ? parent.children.push(n) : parent.children.splice(i, 0, n); },
    remove(n) { n.parent.children.splice(n.parent.children.indexOf(n), 1); },
});
const nodes = n => [n, ...n.children.flatMap(nodes)];
const text = root => nodes(root).filter(n => n.type !== '#comment').map(n => n.text).join(' ');
function mount(form, allowed = true) {
    const props = reactive({form, canEditPhone: allowed});
    const root = node('root');
    const app = renderer.createApp({render: () => h(Summary, props)});
    app.mount(root);
    return {root, props, app};
}
const button = root => nodes(root).find(n => n.type === 'button');

test('summary handles absent and partial names, live multiline description, and no invented id', async () => {
    const form = reactive({});
    const {root, app} = mount(form);
    assert.match(text(root), /Не указано/);
    assert.doesNotMatch(text(root), /undefined|null/);
    form.applicant_info = {lastname: '  Иванов  ', firstname: '  ', surname: ' Петрович '};
    form.description = 'Первая строка\n<script>не HTML</script>';
    await nextTick();
    assert.match(text(root), /Иванов Петрович/);
    assert.match(text(root), /Первая строка\n<script>не HTML<\/script>/);
    assert.equal(nodes(root).some(n => n.type === 'script'), false);
    app.unmount();
});

test('phone draft changes only after confirmation and preserves other applicant fields', async () => {
    const form = reactive({id: 42, applicant_info: {phone: '7000', lastname: 'Иванов', street: 'Мира'}});
    const {root, app} = mount(form);
    assert.equal(button(root).props.type, 'button');
    button(root).props.onClick();
    await nextTick();
    let input = nodes(root).find(n => n.type === 'test-input');
    assert.equal(input.props.modelValue, '7000');
    input.props['onUpdate:modelValue']('7999');
    assert.equal(form.applicant_info.phone, '7000');
    let prevented = false;
    input.props.onKeydown[0]({key: 'Enter', preventDefault() {prevented = true;}, stopPropagation() {}});
    assert.equal(prevented, true);
    button(root).props.onClick();
    await nextTick();
    assert.deepEqual({...form.applicant_info}, {phone: '7999', lastname: 'Иванов', street: 'Мира'});
    assert.equal(nodes(root).some(n => n.type === 'test-input'), false);
    assert.match(text(root), /7999/);
    app.unmount();
});

test('view mode removes editing and discards an unconfirmed draft', async () => {
    const form = reactive({applicant_info: {phone: '7000'}});
    const {root, props, app} = mount(form);
    button(root).props.onClick();
    await nextTick();
    nodes(root).find(n => n.type === 'test-input').props['onUpdate:modelValue']('7999');
    props.canEditPhone = false;
    await nextTick();
    assert.equal(button(root), undefined);
    assert.equal(nodes(root).some(n => n.type === 'test-input'), false);
    assert.equal(form.applicant_info.phone, '7000');
    app.unmount();
});

test('incident tabs switch distinct cached components and preserve drafts', async () => {
    const edit = await readFile(new URL('../../resources/js/Pages/Incidents/Edit.vue', import.meta.url), 'utf8');
    const template = edit.match(/<keep-alive>\s*<component v-if="tabs\[currentTab\]\?\.show"[\s\S]*?<\/keep-alive>/)?.[0];
    assert.ok(template, 'Test the actual tab outlet from Edit.vue');
    const output = compileTemplate({source: template, filename: 'Edit.vue', id: 'tabs-test'});
    assert.deepEqual(output.errors, []);
    const code = output.code.replace(/from ["']vue["']/g, `from '${import.meta.resolve('vue')}'`);
    const {render} = await import('data:text/javascript;base64,' + Buffer.from(code).toString('base64'));
    const mounted = {};
    const tabs = Object.fromEntries(['UKIO', 'EDDS', 'FIREFIGHTERS'].map(name => [name, {
        show: true,
        template: markRaw({setup() {
            mounted[name] = (mounted[name] ?? 0) + 1;
            const draft = reactive({value: name});
            return () => h('input', {value: draft.value, onInput: value => draft.value = value});
        }}),
    }]));
    const state = reactive({tabs, currentTab: 'UKIO', form: {}, createMode: false});
    const root = node('root');
    const app = renderer.createApp({setup: () => state, render});
    app.mount(root);
    nodes(root).find(n => n.type === 'input').props.onInput('unsaved UKIO');
    for (const name of ['EDDS', 'FIREFIGHTERS', 'UKIO', 'FIREFIGHTERS', 'EDDS', 'UKIO']) {
        state.currentTab = name;
        await nextTick();
        assert.equal(nodes(root).find(n => n.type === 'input').props.value, name === 'UKIO' ? 'unsaved UKIO' : name);
    }
    assert.deepEqual(mounted, {UKIO: 1, EDDS: 1, FIREFIGHTERS: 1});
    state.tabs.UKIO.show = false;
    await nextTick();
    assert.equal(nodes(root).some(n => n.type === 'input'), false);
    app.unmount();
});
