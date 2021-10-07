import Sortable from 'sortablejs';

document.addEventListener("DOMContentLoaded", () => {
    let guides = document.getElementsByClassName('sortable-guides');

    [...guides].forEach( list => {
        Sortable.create(list, {
            onChange: function() {
                let ids = this.toArray();
                let order = document.getElementById('guides-order-' + list.dataset.screenId);
                order.value = ids.join(',');
            }
        });
    });
});
