export const getEDDSDefaults = (report = null) => ({
    id: report?.id ?? null,
    condition: report?.condition ?? 2,
    type: report?.type ?? null,
    company: report?.company ?? null,
    instruction: report?.instruction ?? null,
    message: report?.message ?? null,
    additional_info: report?.additional_info ?? null,
    elimination_datetime: report?.elimination_datetime ?? null,
    is_consultation: report?.is_consultation ?? false,
    results: {...report?.results},
    responses: report?.responses ?? []
});
