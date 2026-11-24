// The little JavaScript of Pop Quiz: everything works without it, it only
// adds shortcuts, confetti, and more questions in the form of a quiz.

const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;

// 1 to 4, or A to D, answer the question
const choices = document.querySelector('[data-choices]');
if (choices) {
    const buttons = [...choices.querySelectorAll('button')];
    document.addEventListener('keydown', (event) => {
        if (event.altKey || event.ctrlKey || event.metaKey || event.repeat) {
            return;
        }
        const index = '1234'.indexOf(event.key) + 'abcd'.indexOf(event.key.toLowerCase()) + 1;
        buttons[index]?.click();
    });
}

// A double click would answer the next question too
for (const form of document.querySelectorAll('form[data-once]')) {
    form.addEventListener('submit', (event) => {
        if (form.dataset.sent) {
            event.preventDefault();
        }
        form.dataset.sent = 'yes';
    });
}

const confetti = document.querySelector('[data-confetti]');
if (confetti && !reducedMotion) {
    const colors = ['#ff4fa3', '#2ad4ff', '#b4f22e', '#ff8c1a', '#a47bff', '#ffd60a', '#ffffff'];
    const count = Number(confetti.dataset.confetti);
    for (let i = 0; i < count; i++) {
        const piece = document.createElement('span');
        piece.className = 'confetti';
        piece.style.left = `${Math.random() * 100}vw`;
        piece.style.background = colors[i % colors.length];
        piece.style.borderRadius = 0 === i % 3 ? '50%' : '2px';
        piece.style.setProperty('--drift', `${(Math.random() - 0.5) * 30}vw`);
        piece.style.setProperty('--spin', `${(Math.random() - 0.5) * 1440}deg`);
        piece.style.setProperty('--duration', `${2 + Math.random() * 2}s`);
        piece.style.setProperty('--delay', `${Math.random() * 0.6}s`);
        piece.addEventListener('animationend', () => piece.remove());
        document.body.append(piece);
    }
}

// The questions of a new quiz: from the minimum to the maximum
const builder = document.querySelector('[data-builder]');
if (builder) {
    const list = builder.querySelector('[data-questions]');
    const template = builder.querySelector('template');
    const add = builder.querySelector('[data-add]');
    const min = Number(builder.dataset.min);
    const max = Number(builder.dataset.max);
    let next = list.children.length;

    const refresh = () => {
        const blocks = [...list.children];
        blocks.forEach((block, i) => {
            block.querySelector('[data-number]').textContent = i + 1;
            block.querySelector('[data-remove]').hidden = blocks.length <= min;
        });
        add.hidden = blocks.length >= max;
    };

    add.hidden = false;
    add.addEventListener('click', () => {
        list.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__index__', String(next++)));
        refresh();
        list.lastElementChild.querySelector('input').focus();
    });
    list.addEventListener('click', (event) => {
        const remove = event.target.closest('[data-remove]');
        if (remove) {
            remove.closest('[data-question]').remove();
            refresh();
        }
    });
    refresh();
}
