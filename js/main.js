document.addEventListener('DOMContentLoaded', function() {

    // ============================================
    // DROPDOWN DE PERFIL
    // ============================================
    const profileDropdownBtn = document.getElementById('profileDropdownBtn');
    const profileDropdownMenu = document.getElementById('profileDropdownMenu');

    if (profileDropdownBtn && profileDropdownMenu) {
        profileDropdownBtn.addEventListener('click', function(e) {
            e.preventDefault();
            profileDropdownMenu.classList.toggle('show');
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.profile-container')) {
                profileDropdownMenu.classList.remove('show');
            }
        });

        const dropdownLinks = profileDropdownMenu.querySelectorAll('a');
        dropdownLinks.forEach(link => {
            link.addEventListener('click', function() {
                profileDropdownMenu.classList.remove('show');
            });
        });
    }

    // ============================================
    // DASHBOARD ADMIN — TABS
    // ============================================
    const urlParams = new URLSearchParams(window.location.search);
    const abaParaReabrir = urlParams.get('aba');

    if (abaParaReabrir) {
        if (abaParaReabrir === 'usuario')      switchTab('tab-usuarios');
        if (abaParaReabrir === 'newsletter')   switchTab('tab-newsletter');
        if (abaParaReabrir === 'agendamento')  switchTab('tab-agendamentos');
    }

    document.querySelectorAll('.js-phone-mask').forEach(input => {
        input.value = formatarTelefone(input.value);
        input.addEventListener('input', function() {
            this.value = formatarTelefone(this.value);
        });
    });

    const bookingForm = document.getElementById('bookingForm');
    if (bookingForm) {
        const timeGrid = document.getElementById('timeGrid');

        const mostrarErro = (chave, alvo, invalido) => {
            const msg = bookingForm.querySelector('[data-error-for="' + chave + '"]');
            if (msg) msg.classList.toggle('show', invalido);
            if (alvo) alvo.classList.toggle('input-invalid', invalido);
        };

        const validarCampo = (input) => {
            let invalido = input.value.trim() === '';
            if (!invalido && input.name === 'telefone') {
                const digitos = input.value.replace(/\D/g, '');
                invalido = digitos.length < 10 || digitos.length > 11;
            }
            mostrarErro(input.id, input, invalido);
            return !invalido;
        };

        const camposTexto = bookingForm.querySelectorAll('input[required]:not([type="radio"])');
        camposTexto.forEach(input => {
            input.addEventListener('input', () => {
                if (input.classList.contains('input-invalid')) validarCampo(input);
            });
            input.addEventListener('blur', () => validarCampo(input));
        });

        bookingForm.querySelectorAll('input[name="horario"]').forEach(radio => {
            radio.addEventListener('change', () => mostrarErro('horario', timeGrid, false));
        });

        bookingForm.addEventListener('submit', function(e) {
            let primeiroInvalido = null;

            camposTexto.forEach(input => {
                if (!validarCampo(input) && !primeiroInvalido) primeiroInvalido = input;
            });

            const horarioMarcado = bookingForm.querySelector('input[name="horario"]:checked');
            mostrarErro('horario', timeGrid, !horarioMarcado);
            if (!horarioMarcado && !primeiroInvalido) primeiroInvalido = timeGrid;

            if (primeiroInvalido) {
                e.preventDefault();
                primeiroInvalido.scrollIntoView({ behavior: 'smooth', block: 'center' });
                if (primeiroInvalido.focus) primeiroInvalido.focus({ preventScroll: true });
            }
        });
    }

});

function formatarTelefone(valor) {
    const d = (valor || '').replace(/\D/g, '').slice(0, 11);
    if (d.length === 0) return '';
    if (d.length <= 2)  return '(' + d;
    if (d.length <= 6)  return '(' + d.slice(0, 2) + ') ' + d.slice(2);
    if (d.length <= 10) return '(' + d.slice(0, 2) + ') ' + d.slice(2, 6) + '-' + d.slice(6);
    return '(' + d.slice(0, 2) + ') ' + d.slice(2, 7) + '-' + d.slice(7);
}

// ============================================
// DASHBOARD ADMIN — FUNÇÕES
// ============================================
function switchTab(tabId) {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.remove('active'));

    const clickedBtn = Array.from(document.querySelectorAll('.tab-btn')).find(btn => btn.getAttribute('onclick') && btn.getAttribute('onclick').includes(tabId));
    if (clickedBtn) clickedBtn.classList.add('active');

    const panel = document.getElementById(tabId);
    if (panel) panel.classList.add('active');
}

function confirmarExclusao(item) {
    return confirm("Tem certeza absoluta que deseja remover " + item + "? Esta ação não poderá ser desfeita.");
}

// ============================================
// FORMULÁRIOS DE AUTH (LOGIN / CADASTRO)
// ============================================
function switchForm(formType) {
    const loginCard = document.getElementById('login-card');
    const cadastroCard = document.getElementById('cadastro-card');

    if (loginCard && cadastroCard) {
        if (formType === 'cadastro') {
            loginCard.classList.add('d-none');
            cadastroCard.classList.remove('d-none');
        } else {
            cadastroCard.classList.add('d-none');
            loginCard.classList.remove('d-none');
        }
    }
}

function togglePasswordVisibility(inputId, button) {
    const input = document.getElementById(inputId);
    if (!input) return;

    const icon = button.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        if (icon) {
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    } else {
        input.type = 'password';
        if (icon) {
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    }
}

// ============================================
// NEWSLETTER VIA AJAX (delegação de evento)
// ============================================
document.addEventListener('submit', async function(e) {
    const form = e.target.closest('#newsletter-form');
    if (!form) return;

    e.preventDefault();
    e.stopPropagation();
    console.log('Submit interceptado!');

    const email = form.querySelector('input[name="email"]').value;
    const feedback = document.querySelector('.newsletter-feedback');

    try {
        const response = await fetch('php/newsletter.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'email=' + encodeURIComponent(email)
        });

        const text = await response.text();
        console.log('Resposta do PHP:', text);

        if (text.trim() === 'ok') {
            feedback.textContent = 'Obrigado pela inscrição! Você vai receber nossas novidades em breve.';
            form.reset();
        } else {
            feedback.textContent = 'Por favor, informe um e-mail válido.';
        }
    } catch (err) {
        console.log('Erro:', err);
        feedback.textContent = 'Erro ao enviar. Tente novamente.';
    }
});

// ============================================
// EVENTOS GLOBAIS
// ============================================
window.addEventListener('load', function() {
    const params = new URLSearchParams(window.location.search);

    if (document.getElementById('login-card')) {
        if (params.get('cadastro') === 'sucesso' || params.get('erro') || params.get('form') === 'login') {
            switchForm('login');
        }
        if (params.get('form') === 'cadastro') {
            switchForm('cadastro');
        }
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const profileDropdownMenu = document.getElementById('profileDropdownMenu');
        if (profileDropdownMenu) {
            profileDropdownMenu.classList.remove('show');
        }
    }
});