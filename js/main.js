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
            if (input.type === 'email') {
                const { erro, sugestao } = validarEmail(input.value);
                invalido = !!erro;
                const msg = bookingForm.querySelector('[data-error-for="' + input.id + '"]');
                if (msg && erro) msg.textContent = sugestao ? erro + ' Você quis dizer ' + sugestao + '?' : erro;
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

        // Mantém o profissional escolhido quando a data é trocada (o formulário de data recarrega a página)
        const proGrid = document.getElementById('proGrid');
        const dateFormProfissional = document.getElementById('dateFormProfissional');
        bookingForm.querySelectorAll('input[name="profissional"]').forEach(radio => {
            radio.addEventListener('change', () => {
                mostrarErro('profissional', proGrid, false);
                if (dateFormProfissional) dateFormProfissional.value = radio.value;
            });
        });

        bookingForm.addEventListener('submit', function(e) {
            let primeiroInvalido = null;

            camposTexto.forEach(input => {
                if (!validarCampo(input) && !primeiroInvalido) primeiroInvalido = input;
            });

            const profissionalMarcado = bookingForm.querySelector('input[name="profissional"]:checked');
            if (proGrid) {
                mostrarErro('profissional', proGrid, !profissionalMarcado);
                if (!profissionalMarcado && !primeiroInvalido) primeiroInvalido = proGrid;
            }

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

    // ============================================
    // DASHBOARD ADMIN — NOVOS AGENDAMENTOS EM TEMPO REAL
    // ============================================
    const notificationBell = document.getElementById('notificationBell');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const notificationCount = document.getElementById('notificationCount');
    const notificationList = document.getElementById('notificationList');
    const notificationError = document.getElementById('notificationError');
    const markAllSeen = document.getElementById('markAllSeen');

    if (notificationBell && notificationDropdown && notificationCount && notificationList && notificationError && markAllSeen) {
        let atualizacaoEmAndamento = null;

        const exibirErroNotificacao = mensagem => {
            notificationError.textContent = mensagem;
            notificationError.hidden = !mensagem;
        };

        const renderizarNotificacoes = agendamentos => {
            notificationList.replaceChildren();
            markAllSeen.hidden = agendamentos.length === 0;

            if (agendamentos.length === 0) {
                notificationList.appendChild(criarElemento('p', 'notification-empty', 'Nenhum agendamento novo.'));
                return;
            }

            agendamentos.forEach(ag => {
                const item = criarElemento('div', 'notification-item');
                const detalhes = criarElemento('div', 'notification-details');
                detalhes.appendChild(criarElemento('strong', '', ag.cliente));
                detalhes.appendChild(criarElemento('span', '', ag.servico));
                detalhes.appendChild(criarElemento('span', '', ag.data + ' às ' + ag.horario));

                const botao = criarElemento('button', 'notification-mark-one', 'Marcar como visto');
                botao.type = 'button';
                botao.dataset.action = 'mark-one';
                botao.dataset.id = ag.id;

                item.append(detalhes, botao);
                notificationList.appendChild(item);
            });
        };

        const atualizarAgendamentos = async (forcar = false) => {
            if (atualizacaoEmAndamento) {
                if (!forcar) return atualizacaoEmAndamento;
                await atualizacaoEmAndamento;
            }

            const requisicao = (async () => {
                try {
                    const resposta = await fetch('novos-agendamentos.php', { cache: 'no-store' });
                    const dados = await resposta.json();
                    if (!resposta.ok || !Array.isArray(dados.novos)) {
                        throw new Error(dados.erro || 'Resposta inválida ao buscar agendamentos.');
                    }

                    [...dados.novos].reverse().forEach(ag => {
                        const linhaExistente = document.querySelector('[data-agendamento-id="' + ag.id + '"]');
                        if (!linhaExistente) adicionarLinhaAgendamento(ag);
                    });

                    [
                        ['statAtivos', dados.total_ativos],
                        ['statUsuarios', dados.total_usuarios],
                        ['statNewsletter', dados.total_newsletter],
                        ['statCancelados', dados.total_cancelados]
                    ].forEach(([id, total]) => {
                        const card = document.getElementById(id);
                        if (card) card.textContent = total;
                    });

                    notificationCount.textContent = dados.total_novos;
                    notificationCount.hidden = Number(dados.total_novos) === 0;
                    notificationBell.setAttribute('aria-label', 'Notificações de novos agendamentos: ' + dados.total_novos);
                    renderizarNotificacoes(dados.novos);
                    exibirErroNotificacao('');
                } catch (erro) {
                    console.error('Não foi possível atualizar as notificações do dashboard.', erro);
                    exibirErroNotificacao('Não foi possível atualizar as notificações. Tente novamente em instantes.');
                }
            })();

            atualizacaoEmAndamento = requisicao;
            try {
                await requisicao;
            } finally {
                if (atualizacaoEmAndamento === requisicao) atualizacaoEmAndamento = null;
            }
        };

        const fecharDropdown = () => {
            notificationDropdown.hidden = true;
            notificationBell.setAttribute('aria-expanded', 'false');
        };

        notificationBell.addEventListener('click', () => {
            const abrir = notificationDropdown.hidden;
            notificationDropdown.hidden = !abrir;
            notificationBell.setAttribute('aria-expanded', String(abrir));
        });

        document.addEventListener('click', evento => {
            if (!notificationBell.parentElement.contains(evento.target)) fecharDropdown();
        });

        document.addEventListener('keydown', evento => {
            if (evento.key === 'Escape') fecharDropdown();
        });

        notificationDropdown.addEventListener('click', async evento => {
            const botao = evento.target.closest('button[data-action]');
            if (!botao || botao.disabled) return;

            const marcarTodos = botao.dataset.action === 'mark-all';
            const dadosPost = marcarTodos
                ? new URLSearchParams({ todos: '1' })
                : new URLSearchParams({ id: botao.dataset.id });

            botao.disabled = true;
            exibirErroNotificacao('');

            try {
                const resposta = await fetch('marcar-visto.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
                    body: dadosPost
                });
                const resultado = await resposta.json();
                if (!resposta.ok || !resultado.sucesso) {
                    throw new Error(resultado.erro || 'Não foi possível marcar o agendamento como visto.');
                }
                await atualizarAgendamentos(true);
            } catch (erro) {
                console.error('Não foi possível marcar as notificações como vistas.', erro);
                exibirErroNotificacao('Não foi possível marcar como visto. Tente novamente.');
                botao.disabled = false;
            }
        });

        markAllSeen.dataset.action = 'mark-all';
        atualizarAgendamentos();
        setInterval(atualizarAgendamentos, 30000);
    }

    // ============================================
    // VALIDAÇÃO DE E-MAIL (LOGIN / CADASTRO)
    // ============================================
    document.querySelectorAll('.auth-form input[type="email"]').forEach(input => {
        const msg = document.querySelector('[data-error-for="' + input.id + '"]');

        const validar = () => {
            const { erro, sugestao } = validarEmail(input.value);
            input.classList.toggle('input-invalid', !!erro);
            if (msg) {
                msg.textContent = erro;
                if (sugestao) {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'email-suggestion';
                    btn.dataset.sugestao = sugestao;
                    btn.textContent = sugestao;
                    msg.append(' Você quis dizer ', btn, '?');
                }
                msg.classList.toggle('show', !!erro);
            }
            return !erro;
        };

        input.addEventListener('blur', validar);
        input.addEventListener('input', () => {
            if (input.classList.contains('input-invalid')) validar();
        });
        input.form.addEventListener('submit', e => {
            if (!validar()) {
                e.preventDefault();
                input.focus();
            }
        });

        // Clique na sugestão corrige o domínio automaticamente
        if (msg) {
            msg.addEventListener('click', e => {
                const sugestao = e.target.closest('[data-sugestao]');
                if (!sugestao) return;
                input.value = sugestao.dataset.sugestao;
                validar();
            });
        }
    });

    // ============================================
    // LOCALIZAÇÃO — DESTACA O DIA DE HOJE NOS HORÁRIOS
    // ============================================
    const hoursList = document.getElementById('hoursList');
    if (hoursList) {
        const dia = String(new Date().getDay());
        hoursList.querySelectorAll('li').forEach(li => {
            if (li.dataset.days.split(',').includes(dia)) li.classList.add('today');
        });
    }

    // ============================================
    // CARROSSEL DO BANNER
    // ============================================
    const heroCarousel = document.getElementById('heroCarousel');
    if (heroCarousel) {
        const slides = heroCarousel.querySelectorAll('.carousel-slide');
        const dots = heroCarousel.querySelectorAll('.carousel-dots button');
        let atual = 0;
        let timer;

        const irPara = (indice) => {
            atual = (indice + slides.length) % slides.length;
            slides.forEach((slide, i) => slide.classList.toggle('active', i === atual));
            dots.forEach((dot, i) => dot.classList.toggle('active', i === atual));
        };

        const reiniciarTimer = () => {
            clearInterval(timer);
            timer = setInterval(() => irPara(atual + 1), 6000);
        };

        heroCarousel.querySelector('.prev').addEventListener('click', () => { irPara(atual - 1); reiniciarTimer(); });
        heroCarousel.querySelector('.next').addEventListener('click', () => { irPara(atual + 1); reiniciarTimer(); });
        dots.forEach((dot, i) => dot.addEventListener('click', () => { irPara(i); reiniciarTimer(); }));

        // Arrastar com o dedo no celular
        let inicioX = null;
        heroCarousel.addEventListener('touchstart', e => { inicioX = e.touches[0].clientX; }, { passive: true });
        heroCarousel.addEventListener('touchend', e => {
            if (inicioX === null) return;
            const diferenca = e.changedTouches[0].clientX - inicioX;
            if (Math.abs(diferenca) > 40) { irPara(atual + (diferenca < 0 ? 1 : -1)); reiniciarTimer(); }
            inicioX = null;
        });

        // Pausa enquanto o mouse está sobre o banner
        heroCarousel.addEventListener('mouseenter', () => clearInterval(timer));
        heroCarousel.addEventListener('mouseleave', reiniciarTimer);

        reiniciarTimer();
    }

    // ============================================
    // PAGAMENTO (SIMULAÇÃO)
    // ============================================
    const paymentBox = document.getElementById('paymentBox');
    if (paymentBox) {
        paymentBox.querySelectorAll('.pay-method').forEach(btn => {
            btn.addEventListener('click', () => {
                paymentBox.querySelectorAll('.pay-method').forEach(b => b.classList.toggle('active', b === btn));
                paymentBox.querySelectorAll('.pay-panel').forEach(p => p.classList.toggle('active', p.dataset.panel === btn.dataset.method));
            });
        });

        paymentBox.querySelectorAll('.btn-copy').forEach(btn => {
            btn.addEventListener('click', () => {
                const campo = document.getElementById(btn.dataset.copy);
                campo.select();
                (navigator.clipboard ? navigator.clipboard.writeText(campo.value) : Promise.reject())
                    .catch(() => document.execCommand('copy'))
                    .finally(() => {
                        btn.textContent = 'Copiado!';
                        setTimeout(() => { btn.textContent = 'Copiar'; }, 1800);
                    });
            });
        });

        // Gera o QR Code a partir do código Pix copia e cola
        const pixQr = document.getElementById('pixQr');
        const pixCode = document.getElementById('pixCode');
        if (pixQr && pixCode && window.QRCode) {
            new QRCode(pixQr, { text: pixCode.value, width: 180, height: 180, colorDark: '#000000', colorLight: '#ffffff', correctLevel: QRCode.CorrectLevel.M });
        }

        const btnSimular = document.getElementById('btnSimularPagamento');
        btnSimular.addEventListener('click', () => {
            btnSimular.disabled = true;
            btnSimular.textContent = 'Processando pagamento...';
            setTimeout(() => {
                paymentBox.querySelectorAll('.pay-methods, .pay-panel, #btnSimularPagamento').forEach(el => el.classList.add('d-none'));
                document.getElementById('paySuccess').classList.remove('d-none');
            }, 1600);
        });
    }

});

// Retorna { erro, sugestao } — erro vazio quando o e-mail está correto
function validarEmail(valor) {
    const email = (valor || '').trim().toLowerCase();
    if (email === '') return { erro: 'Informe seu e-mail.' };
    if (!email.includes('@')) return { erro: 'O e-mail precisa ter "@". Ex: maria@gmail.com' };
    if (!/^[^\s@]+@[^\s@]+\.[a-z]{2,}$/.test(email)) return { erro: 'E-mail incompleto. Ex: maria@gmail.com' };

    // Detecta erros comuns de digitação no domínio
    const dominio = email.split('@')[1];
    const correcoes = {
        'gmail.com':    ['gmial.com', 'gmal.com', 'gmaill.com', 'gamil.com', 'gnail.com', 'gmail.con', 'gmail.co', 'gmail.cm', 'gmail.om', 'gmail.comm', 'gmai.com', 'gmail.com.br'],
        'hotmail.com':  ['hotmial.com', 'hotmal.com', 'hotmail.con', 'hotmail.co', 'hotmai.com', 'hotmaill.com', 'homail.com'],
        'outlook.com':  ['outlok.com', 'outllok.com', 'outlook.con', 'outlook.co', 'otlook.com'],
        'yahoo.com.br': ['yaho.com.br', 'yahoo.com.bt', 'yahooo.com.br']
    };
    for (const certo in correcoes) {
        if (correcoes[certo].includes(dominio)) {
            return { erro: 'Verifique o domínio do e-mail.', sugestao: email.split('@')[0] + '@' + certo };
        }
    }
    return { erro: '' };
}

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

// Cria um elemento com classe e texto (o texto nunca é interpretado como HTML)
function criarElemento(tag, classe, texto) {
    const el = document.createElement(tag);
    if (classe) el.className = classe;
    if (texto !== undefined) el.textContent = texto;
    return el;
}

// Insere o novo agendamento no topo da tabela, destacado
function adicionarLinhaAgendamento(ag) {
    const cancelado = ag.status === 'Cancelado';
    const lista = document.getElementById(cancelado ? 'listaCancelados' : 'listaAgendamentos');
    if (!lista) return;

    document.getElementById(cancelado ? 'tabelaCancelados' : 'tabelaAgendamentos').classList.remove('d-none');
    document.getElementById(cancelado ? 'canceladosVazio' : 'agendamentosVazio').classList.add('d-none');

    const tr = criarElemento('tr');
    tr.dataset.agendamentoId = ag.id;

    const tdCliente = criarElemento('td');
    tdCliente.appendChild(criarElemento('strong', '', ag.cliente));
    tr.appendChild(tdCliente);
    tr.appendChild(criarElemento('td', '', ag.telefone));
    tr.appendChild(criarElemento('td', '', ag.servico));

    const tdData = criarElemento('td');
    tdData.appendChild(criarElemento('span', 'tag-date', ag.data + ' às ' + ag.horario + 'h'));
    tr.appendChild(tdData);

    const tdStatus = criarElemento('td');
    tdStatus.appendChild(criarElemento('span', 'status-badge ' + (cancelado ? 'status-cancelado' : 'status-agendado'), cancelado ? 'CANCELADO' : ag.status));
    tr.appendChild(tdStatus);

    // Mesmo formulário de remoção usado nas linhas geradas pelo PHP
    const tdAcoes = criarElemento('td');
    tdAcoes.style.textAlign = 'right';
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'excluir.php';
    form.style.display = 'inline';
    form.addEventListener('submit', e => {
        if (!confirmarExclusao('este agendamento')) e.preventDefault();
    });
    [['id', ag.id], ['tipo', 'agendamento']].forEach(([nome, valor]) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = nome;
        input.value = valor;
        form.appendChild(input);
    });
    const btn = criarElemento('button', 'btn-delete', 'Remover');
    btn.type = 'submit';
    form.appendChild(btn);
    tdAcoes.appendChild(form);
    tr.appendChild(tdAcoes);

    lista.prepend(tr);
}

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

    const email = form.querySelector('input[name="email"]').value;
    const feedback = document.querySelector('.newsletter-feedback');

    const { erro, sugestao } = validarEmail(email);
    if (erro) {
        feedback.textContent = sugestao ? erro + ' Você quis dizer ' + sugestao + '?' : erro;
        return;
    }

    try {
        const response = await fetch('php/newsletter.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'email=' + encodeURIComponent(email)
        });

        const text = await response.text();

        if (text.trim() === 'ok') {
            feedback.textContent = 'Obrigado pela inscrição! Você vai receber nossas novidades em breve.';
            form.reset();
        } else {
            feedback.textContent = 'Por favor, informe um e-mail válido.';
        }
    } catch (err) {
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