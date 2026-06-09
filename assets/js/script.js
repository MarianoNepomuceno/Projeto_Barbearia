let usuarioLogado = null;
let precos = {};

const form = document.getElementById("formAgendamento");
const totalSpan = document.getElementById("total");
const selectHora = document.getElementById("hora");
const mensagem = document.getElementById("mensagem");
const comprovante = document.getElementById("comprovante");
const comprovanteConteudo = document.getElementById("comprovanteConteudo");
const inputData = document.getElementById("data");
const usuarioInfo = document.getElementById("usuarioInfo");
const btnLogout = document.getElementById("btnLogout");

const barba = document.getElementById("barba");
const sobrancelha = document.getElementById("sobrancelha");

async function carregarPrecos() {
  const data = await apiRequest("precos.php");
  precos = data.precos;
  mostrarPrecos();
}

function mostrarPrecos() {
  if (window.precoDegrade) precoDegrade.textContent = precos["Degradê"].toFixed(2);
  if (window.precoSocial) precoSocial.textContent = precos["Social"].toFixed(2);
  if (window.precoTesoura) precoTesoura.textContent = precos["Tesoura"].toFixed(2);
  if (window.precoBarba) precoBarba.textContent = precos["Barba"].toFixed(2);
  if (window.precoSobrancelha) precoSobrancelha.textContent = precos["Sobrancelha"].toFixed(2);
}

function gerarHorarios() {
  if (!selectHora) return;

  selectHora.innerHTML = '<option value="">Selecione o horário</option>';

  const inicio = 9 * 60;
  const fim = 18 * 60;

  for (let m = inicio; m <= fim; m += 30) {
    if (m >= 12 * 60 && m < 13 * 60 + 30) continue;

    const h = String(Math.floor(m / 60)).padStart(2, "0");
    const min = String(m % 60).padStart(2, "0");
    const hora = `${h}:${min}`;

    const opt = document.createElement("option");
    opt.value = hora;
    opt.textContent = hora;
    selectHora.appendChild(opt);
  }
}

let horariosOcupados = [];
let horariosBloqueados = [];

async function carregarDisponibilidade(data) {
  if (!data) return;

  const disponibilidade = await apiRequest(`disponibilidade.php?data=${encodeURIComponent(data)}`);
  horariosOcupados = disponibilidade.ocupados;
  horariosBloqueados = disponibilidade.bloqueados;
  atualizarHorariosDisponiveis();
}

function atualizarHorariosDisponiveis() {
  const data = inputData.value;
  if (!data) return;

  const ocupados = horariosOcupados;
  const bloqueados = horariosBloqueados;
  const agora = new Date();

  [...selectHora.options].forEach((opt) => {
    if (!opt.value) return;

    opt.disabled = false;
    opt.textContent = opt.value;

    const dataHoraSelecionada = new Date(`${data}T${opt.value}:00`);
    const horarioPassado = dataHoraSelecionada < agora;

    if (ocupados.includes(opt.value) || bloqueados.includes(opt.value) || horarioPassado) {
      opt.disabled = true;
      opt.textContent = `${opt.value} (Indisponível)`;
    }
  });
}

function calcularTotal() {
  let total = 0;

  const corte = document.querySelector('input[name="corte"]:checked');
  if (corte) total += precos[corte.value];

  if (barba && barba.checked) total += precos["Barba"];
  if (sobrancelha && sobrancelha.checked) total += precos["Sobrancelha"];

  totalSpan.textContent = total.toFixed(2);
}

function mostrarMensagem(texto, tipo) {
  mensagem.textContent = texto;
  mensagem.className = `mensagem ${tipo}`;
  mensagem.style.display = "block";
}

function imprimirComprovante() {
  window.print();
}

async function inicializarPagina() {
  usuarioLogado = await requireCliente();

  if (!usuarioLogado) return;

  const telefone = usuarioLogado.telefone ? ` | ${usuarioLogado.telefone}` : "";
  usuarioInfo.textContent = `Olá, ${usuarioLogado.nome}${telefone}`;

  const hoje = new Date().toISOString().split("T")[0];
  inputData.min = hoje;

  await carregarPrecos();
  gerarHorarios();

  inputData.addEventListener("change", async () => {
    gerarHorarios();
    await carregarDisponibilidade(inputData.value);
  });

  document.querySelectorAll('input[name="corte"], #barba, #sobrancelha')
    .forEach((el) => el.addEventListener("change", calcularTotal));

  btnLogout.addEventListener("click", () => logout("login.html"));

  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    const data = inputData.value;
    const hora = selectHora.value;

    if (!hora) {
      mostrarMensagem("Selecione um horário válido.", "erro");
      return;
    }

    const corte = document.querySelector('input[name="corte"]:checked');

    if (!corte && !barba.checked && !sobrancelha.checked) {
      mostrarMensagem("Selecione pelo menos um serviço.", "erro");
      return;
    }

    const servicos = [];
    if (corte) servicos.push(corte.value);
    if (barba.checked) servicos.push("Barba");
    if (sobrancelha.checked) servicos.push("Sobrancelha");

    const total = totalSpan.textContent;

    try {
      const resultado = await apiRequest("agendamentos.php", {
        method: "POST",
        body: JSON.stringify({
          data,
          hora,
          servicos,
          total,
        }),
      });

      const agendamento = resultado.agendamento;
      mostrarMensagem("Agendamento realizado com sucesso!", "sucesso");

      comprovanteConteudo.innerHTML = `
        <p>Cliente: ${escapeHtml(agendamento.nome)}</p>
        <p>WhatsApp: ${escapeHtml(agendamento.telefone)}</p>
        <p>Data: ${escapeHtml(agendamento.data)}</p>
        <p>Hora: ${escapeHtml(agendamento.hora)}</p>
        <hr>
        <p>Serviços:</p>
        <p>${escapeHtml(agendamento.servicos.join(", "))}</p>
        <hr>
        <p><strong>Total: R$ ${escapeHtml(agendamento.total)}</strong></p>
      `;

      comprovante.style.display = "block";

      form.reset();
      totalSpan.textContent = "0.00";

      gerarHorarios();
      await carregarDisponibilidade(data);
    } catch (error) {
      mostrarMensagem(error.message, "erro");
    }
  });
}

inicializarPagina();
