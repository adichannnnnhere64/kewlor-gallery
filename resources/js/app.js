import sort from '@alpinejs/sort';

Alpine.plugin(sort);

import Quill from 'quill';
window.Quill = Quill;


document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        const toolbars = document.querySelectorAll('.ql-toolbar.ql-snow');
        if (toolbars.length > 1) {
            // Remove the first toolbar
            toolbars[0].remove();

            // Ensure the remaining toolbar is visible
            if (toolbars[1]) {
                toolbars[1].style.display = '';
            }
        }
    }, 100);
});
