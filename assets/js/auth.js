const APP_ROOT = window.location.pathname.includes("/Projeto_Barbearia/")
  ? "/Projeto_Barbearia/"
  : "/";

function appPath(path) {
  return `${APP_ROOT}${path.replace(/^\/+/, "")}`;
}

function pagePath(path) {
  return appPath(`pages/${path.replace(/^\/+/, "")}`);
}

function normalizeRedirectPath(path) {
  if (!path) {
    return pagePath("login.html");
  }

  if (path.startsWith("/") || path.startsWith("../") || path.startsWith("./")) {
    return path;
  }

  if (path.startsWith("pages/") || path === "index.html") {
    return appPath(path);
  }

  return pagePath(path);
}

const API_BASE = appPath("api");

async function apiRequest(path, options = {}) {
  const config = {
    credentials: "same-origin",
    headers: {
      "Content-Type": "application/json",
      ...(options.headers || {}),
    },
    ...options,
  };

  const response = await fetch(`${API_BASE}/${path}`, config);
  const data = await response.json().catch(() => ({}));

  if (!response.ok) {
    throw new Error(data.message || "Erro na requisição.");
  }

  return data;
}

async function getCurrentUser() {
  try {
    const data = await apiRequest("auth/me.php");
    return data.usuario;
  } catch {
    return null;
  }
}

async function requireCliente(redirectTo = "login.html") {
  const usuario = await getCurrentUser();

  if (!usuario || usuario.tipo !== "cliente") {
    window.location.href = normalizeRedirectPath(redirectTo);
    return null;
  }

  return usuario;
}

function redirectByTipo(tipo) {
  return tipo === "admin" ? pagePath("admin.html") : appPath("index.html");
}

async function requireAdmin(redirectTo = "login.html") {
  const usuario = await getCurrentUser();

  if (!usuario || usuario.tipo !== "admin") {
    window.location.href = normalizeRedirectPath(redirectTo);
    return null;
  }

  return usuario;
}

async function logout(redirectTo = "login.html") {
  try {
    await apiRequest("auth/logout.php", { method: "POST" });
  } catch {
    // Redireciona mesmo se a sessão já tiver expirado.
  }

  window.location.href = normalizeRedirectPath(redirectTo);
}

function escapeHtml(texto) {
  return String(texto)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#39;");
}

function mostrarMensagemAuth(elemento, texto, tipo) {
  elemento.textContent = texto;
  elemento.className = `mensagem ${tipo}`;
  elemento.style.display = "block";
}
