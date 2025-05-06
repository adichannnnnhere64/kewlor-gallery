import sort from '@alpinejs/sort';

Alpine.plugin(sort);

import Quill from 'quill';
window.Quill = Quill;


function fixQuillToolbar() {
    setTimeout(() => {
        const toolbars = document.querySelectorAll('.ql-toolbar.ql-snow');
        if (toolbars.length > 1) {
            // Remove all but the last toolbar
            toolbars.forEach((toolbar, index) => {
                if (index !== toolbars.length - 1) {
                    toolbar.remove();
                }
            });
        }
    }, 100);
}



document.addEventListener('DOMContentLoaded', fixQuillToolbar);


window.addEventListener('edit-content', event => {
    setTimeout(() => {
        // Only target toolbars inside .edit blocks (edit mode)
        document.querySelectorAll('.edit').forEach((editBlock) => {
            const toolbars = editBlock.querySelectorAll('.ql-toolbar.ql-snow');
            if (toolbars.length > 1) {
                toolbars.forEach((toolbar, index) => {
                    if (index !== toolbars.length - 1) {
                        toolbar.remove();
                    }
                });
            }
        });
    }, 50);

})


