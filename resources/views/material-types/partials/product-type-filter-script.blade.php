<script>
document.addEventListener('DOMContentLoaded', function () {
    const group = document.getElementById('material_product_group_id');
    const type = document.getElementById('material_product_type_id');

    if (!group || !type) return;

    const selectedTypeId = @json((string) ($selectedProductTypeId ?? ''));
    const original = Array.from(type.options).map(option => ({
        value: option.value,
        text: option.textContent,
        group: option.dataset.group || ''
    }));

    function refreshTypes() {
        const groupId = group.value;

        type.innerHTML = '';
        type.add(new Option('Select Product Type', ''));

        original.forEach(function (option) {
            if (!option.value) return;

            if (groupId && option.group === groupId) {
                const next = new Option(option.text, option.value);
                next.dataset.group = option.group;
                next.selected = option.value === selectedTypeId;
                type.add(next);
            }
        });
    }

    group.addEventListener('change', function () {
        type.innerHTML = '';
        type.add(new Option('Select Product Type', ''));

        const groupId = group.value;
        original.forEach(function (option) {
            if (!option.value) return;

            if (groupId && option.group === groupId) {
                const next = new Option(option.text, option.value);
                next.dataset.group = option.group;
                type.add(next);
            }
        });
    });

    refreshTypes();
});
</script>
