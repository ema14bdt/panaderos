(function() {
    'use strict';

    function reindexContainer(container, arrayName, labelPrefix) {
        var items = container.querySelectorAll('.repeater-item');
        items.forEach(function(item, idx) {
            item.setAttribute('data-index', idx);
            var num = item.querySelector('.item-number');
            if (num) {
                num.textContent = idx + 1;
            }
            var titleSpan = item.querySelector('.item-title-preview');
            if (titleSpan) {
                var input = item.querySelector('input[type="text"]');
                if (input && input.value) {
                    titleSpan.textContent = ': ' + input.value;
                }
            }
            item.querySelectorAll('input, select, textarea').forEach(function(input) {
                var name = input.getAttribute('name');
                if (name) {
                    var regex = new RegExp(arrayName + '\\[\\d+\\]');
                    input.setAttribute('name', name.replace(regex, arrayName + '[' + idx + ']'));
                }
            });
        });
    }

    function initRepeaters() {
        document.addEventListener('click', function(e) {
            var target = e.target;
            if (!target) return;

            // Remove button
            if (target.matches('.btn-remove') || target.closest('.btn-remove')) {
                var btn = target.matches('.btn-remove') ? target : target.closest('.btn-remove');
                var container = btn.closest('.repeater-list');
                if (!container) return;

                var items = container.querySelectorAll('.repeater-item');
                if (items.length <= 1) {
                    alert('Debe haber al menos un elemento en la lista.');
                    return;
                }

                var item = btn.closest('.repeater-item');
                if (item) {
                    var arrayName = container.getAttribute('data-array-name') || 'items';
                    item.remove();
                    reindexContainer(container, arrayName);
                }
            }

            // Add button
            if (target.matches('.btn-add') || target.closest('.btn-add')) {
                var addBtn = target.matches('.btn-add') ? target : target.closest('.btn-add');
                var templateId = addBtn.getAttribute('data-template');
                var targetContainerId = addBtn.getAttribute('data-target');

                if (templateId && targetContainerId) {
                    var template = document.getElementById(templateId);
                    var list = document.getElementById(targetContainerId);
                    if (template && list) {
                        var count = list.querySelectorAll('.repeater-item').length;
                        var arrayName = list.getAttribute('data-array-name') || 'items';
                        var clone = template.content.cloneNode(true);
                        var tempDiv = document.createElement('div');
                        tempDiv.appendChild(clone);
                        
                        var html = tempDiv.innerHTML.replace(/__INDEX__/g, count).replace(/__NUMBER__/g, count + 1);
                        list.insertAdjacentHTML('beforeend', html);
                        reindexContainer(list, arrayName);
                    }
                }
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initRepeaters);
    } else {
        initRepeaters();
    }
})();
