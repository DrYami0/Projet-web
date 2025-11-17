document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('gameForm');
    if (!form) {
        return; 
    }

    const blanks = document.querySelectorAll('.blank');
    const words = document.querySelectorAll('.word');
    const submitBtn = document.getElementById('submitBtn');
    const feedback = document.querySelector('.feedback');
    let draggedWord = null;

    // Make words draggable
    words.forEach(function (word) {
        word.addEventListener('dragstart', function (e) {
            draggedWord = e.target;
            e.target.classList.add('dragged');
            if (e.dataTransfer) {
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', '');
            }
        });

        word.addEventListener('dragend', function (e) {
            e.target.classList.remove('dragged');
        });
    });

    // Pre-create hidden inputs for each blank so they can be submitted via POST
    blanks.forEach(function (blank) {
        const order = blank.dataset.order;
        if (!order || !form) return;

        // Hidden input for the word id
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'answers[' + order + '][id]';
        idInput.dataset.order = order;
        form.appendChild(idInput);

        // Hidden input for the order (position of the blank)
        const orderInput = document.createElement('input');
        orderInput.type = 'hidden';
        orderInput.name = 'answers[' + order + '][order]';
        orderInput.value = order;
        form.appendChild(orderInput);
    });

    // Make blanks droppable
    blanks.forEach(function (blank) {
        blank.addEventListener('dragover', function (e) {
            e.preventDefault();
            if (e.dataTransfer) {
                e.dataTransfer.dropEffect = 'move';
            }
        });

        blank.addEventListener('drop', function (e) {
            e.preventDefault();
            if (draggedWord && blank.dataset.filled !== 'true') {
                const droppedWord = draggedWord.cloneNode(true);
                droppedWord.style.position = 'static';
                blank.appendChild(droppedWord);
                blank.dataset.filled = 'true';
                blank.classList.add('filled');

                // Remove from pool (for single-use)
                draggedWord.remove();

                // Update hidden id input corresponding to this blank
                const order = blank.dataset.order;
                const hiddenIdInput = form.querySelector(
                    'input[name="answers[' + order + '][id]"]'
                );
                if (hiddenIdInput) {
                    hiddenIdInput.value = droppedWord.dataset.id;
                }

                draggedWord = null;

                // Enable submit when all blanks are filled
                if (
                    Array.prototype.every.call(blanks, function (b) {
                        return b.dataset.filled === 'true';
                    })
                ) {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                    }
                }
            }
        });
    });

    // Disable submit until all filled
    if (submitBtn) {
        submitBtn.disabled = true;
    }
});