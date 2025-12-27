<script>
document.addEventListener('DOMContentLoaded', function () {

    // Toggle enable/disable for any rule (country/city/global)
    document.querySelectorAll('.toggle-rule').forEach(function (toggle) {

        toggle.addEventListener('change', function () {

            const id = this.dataset.id;
            const enabled = this.checked ? 1 : 0;

            fetch(`/admin/pricing/margin-rules/${id}/toggle`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ is_enabled: enabled })
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    alert("Update failed");
                } else {
                    location.reload();
                }
            })
            .catch(() => alert("Network error"));
        });

    });

});
</script>
