<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({ login: '', password: '' });
const submit = () => form.post(route('admin.login.store'), { onFinish: () => form.reset('password') });
</script>

<template>
    <Head title="Вход в администрирование" />
    <main class="min-h-screen bg-gray-100 flex items-center justify-center p-6">
        <form @submit.prevent="submit" class="w-full max-w-sm border border-gray-300 bg-white shadow-sm">
            <h1 class="bg-primary text-white px-5 py-4 text-lg font-semibold">Администрирование справочников</h1>
            <div class="p-5 space-y-4">
                <p class="text-sm text-gray-600">Вход с отдельной учётной записью администратора.</p>
                <div>
                    <label for="login" class="block text-sm mb-1">Логин</label>
                    <input id="login" v-model="form.login" autocomplete="username" autofocus required maxlength="100" class="w-full border-gray-300 rounded text-sm" />
                    <p v-if="form.errors.login" role="alert" class="text-sm text-red-700 mt-1">{{ form.errors.login }}</p>
                </div>
                <div>
                    <label for="password" class="block text-sm mb-1">Пароль</label>
                    <input id="password" type="password" v-model="form.password" autocomplete="current-password" required class="w-full border-gray-300 rounded text-sm" />
                    <p v-if="form.errors.password" role="alert" class="text-sm text-red-700 mt-1">{{ form.errors.password }}</p>
                </div>
                <button :disabled="form.processing" class="w-full bg-primary text-white rounded py-2 disabled:opacity-50">Войти</button>
            </div>
        </form>
    </main>
</template>
