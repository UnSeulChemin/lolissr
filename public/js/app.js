document.addEventListener('DOMContentLoaded', function ()
{

    const livreInput = document.getElementById('livre');
    const slugInput = document.getElementById('slug');

    if (livreInput && slugInput) {

        livreInput.addEventListener('input', function () {

            let slugValue = this.value
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-');

            slugInput.value = slugValue;

        });

    }

});