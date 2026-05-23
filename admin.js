// ELEMENTOS
const lista = document.getElementById("listaAgendamentos");
const bloqueioHora = document.getElementById("bloqueioHora");
const bloqueioData = document.getElementById("bloqueioData");

// DADOS
let agendamentos = JSON.parse(localStorage.getItem("agendamentos")) || [];
let bloqueios = JSON.parse(localStorage.getItem("bloqueios")) || [];

let precos = JSON.parse(localStorage.getItem("precos")) || {
  "Degradê": 30,
  "Social": 25,
  "Tesoura": 35,
  "Barba": 20,
  "Sobrancelha": 10
};

// GERAR HORÁRIOS
function gerarHorariosAdmin() {
  bloqueioHora.innerHTML = '<option value="">Selecione o horário</option>';

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

    bloqueioHora.appendChild(opt);
  }
}

gerarHorariosAdmin();

// PREÇOS
function carregarPrecos() {
  precoDegrade.value = precos["Degradê"];
  precoSocial.value = precos["Social"];
  precoTesoura.value = precos["Tesoura"];
  precoBarba.value = precos["Barba"];
  precoSobrancelha.value = precos["Sobrancelha"];
}
carregarPrecos();

function salvarPrecos() {
  precos = {
    "Degradê": Number(precoDegrade.value),
    "Social": Number(precoSocial.value),
    "Tesoura": Number(precoTesoura.value),
    "Barba": Number(precoBarba.value),
    "Sobrancelha": Number(precoSobrancelha.value)
  };

  localStorage.setItem("precos", JSON.stringify(precos));
  alert("Preços salvos!");
}

// BLOQUEAR HORÁRIO
function horarioEstaBloqueado(data, hora) {
  return bloqueios.some(b => b.data === data && b.hora === hora);
}

function bloquearHorario() {
  const data = bloqueioData.value;
  const hora = bloqueioHora.value;

  if (!data || !hora) {
    alert("Selecione data e horário.");
    return;
  }

  if (horarioEstaBloqueado(data, hora)) {
    alert("Horário já está bloqueado.");
    return;
  }

  bloqueios.push({ data, hora });
  localStorage.setItem("bloqueios", JSON.stringify(bloqueios));

  alert("Horário bloqueado!");
  listar();
}

// LISTAR TUDO
function listar() {
  lista.innerHTML = "";

  agendamentos = JSON.parse(localStorage.getItem("agendamentos")) || [];
  bloqueios = JSON.parse(localStorage.getItem("bloqueios")) || [];

  if (agendamentos.length === 0 && bloqueios.length === 0) {
    lista.innerHTML = "<li>Nenhum registro.</li>";
    return;
  }

  // AGENDAMENTOS
  agendamentos.forEach((a, i) => {
    const li = document.createElement("li");

    li.innerHTML = `
      <strong>${a.nome}</strong> - ${a.telefone}<br>
      📅 ${a.data} às ${a.hora}<br>
      💈 ${a.servicos.join(", ")}<br>
      💰 R$ ${a.total}
      <button onclick="cancelarAgendamento(${i})">Cancelar</button>
    `;

    lista.appendChild(li);
  });

  // BLOQUEIOS
  bloqueios.forEach((b, i) => {
    const li = document.createElement("li");

    li.innerHTML = `
      🔒 <strong>Horário bloqueado</strong><br>
      📅 ${b.data} às ${b.hora}
      <button onclick="removerBloqueio(${i})">Liberar</button>
    `;

    lista.appendChild(li);
  });
}

listar();

// CANCELAR AGENDAMENTO
function cancelarAgendamento(i) {
  if (!confirm("Cancelar agendamento?")) return;

  agendamentos.splice(i, 1);
  localStorage.setItem("agendamentos", JSON.stringify(agendamentos));

  listar();
}

// REMOVER BLOQUEIO
function removerBloqueio(i) {
  if (!confirm("Liberar horário?")) return;

  bloqueios.splice(i, 1);
  localStorage.setItem("bloqueios", JSON.stringify(bloqueios));

  listar();
}