import { useState, type FormEvent } from "react"
import {
  AlertTriangle,
  CalendarDays,
  Eye,
  EyeOff,
  LockKeyhole,
  LogIn,
  Mail,
  PawPrint,
  TrendingUp,
  UserPlus,
  UserRound,
} from "lucide-react"
import { demoUser } from "../../data/mockData"
import type { AppUser } from "../../types/domain"
import Button from "../ui/Button"
import Field from "../ui/Field"

export default function AuthScreen({
  onAuthenticate,
}: {
  onAuthenticate: (user: AppUser) => void
}) {
  const [mode, setMode] = useState<"login" | "register">("login")
  const [showPassword, setShowPassword] = useState(false)
  const [authForm, setAuthForm] = useState({
    name: "",
    email: "",
    password: "",
    confirmPassword: "",
  })
  const [error, setError] = useState("")

  const update = (field: keyof typeof authForm, value: string) => {
    setAuthForm((current) => ({ ...current, [field]: value }))
    setError("")
  }

  const changeMode = (nextMode: "login" | "register") => {
    setMode(nextMode)
    setError("")
    setAuthForm({ name: "", email: "", password: "", confirmPassword: "" })
  }

  const submit = (event: FormEvent) => {
    event.preventDefault()
    const email = authForm.email.trim().toLowerCase()
    const storedUsers = JSON.parse(
      localStorage.getItem("pet-mania-users") || "[]",
    ) as AppUser[]

    if (mode === "login") {
      const user = [demoUser, ...storedUsers].find(
        (item) =>
          item.email.toLowerCase() === email &&
          item.password === authForm.password,
      )
      if (!user) {
        setError(
          "E-mail ou senha incorretos. Verifique os dados e tente novamente.",
        )
        return
      }
      onAuthenticate(user)
      return
    }

    if (
      !authForm.name.trim() ||
      !email ||
      !authForm.password ||
      !authForm.confirmPassword
    ) {
      setError("Preencha todos os campos para criar sua conta.")
      return
    }
    if (authForm.password.length < 6) {
      setError("A senha deve ter pelo menos 6 caracteres.")
      return
    }
    if (authForm.password !== authForm.confirmPassword) {
      setError("As senhas informadas não coincidem.")
      return
    }
    if (
      [demoUser, ...storedUsers].some(
        (item) => item.email.toLowerCase() === email,
      )
    ) {
      setError("Já existe uma conta cadastrada com este e-mail.")
      return
    }

    const newUser: AppUser = {
      name: authForm.name.trim(),
      email,
      password: authForm.password,
      role: "Colaborador(a)",
    }
    localStorage.setItem(
      "pet-mania-users",
      JSON.stringify([...storedUsers, newUser]),
    )
    onAuthenticate(newUser)
  }

  return (
    <main className="auth-page">
      <section className="auth-visual">
        <div className="auth-brand">
          <span className="brand-mark">
            <PawPrint size={24} />
          </span>
          <span>
            Pet <b>Mania</b>
          </span>
        </div>
        <div className="auth-message">
          <span>GESTÃO QUE CUIDA</span>
          <h1>Mais organização para você. Mais carinho para eles.</h1>
          <p>
            Agenda, clientes, serviços e resultados financeiros reunidos em um
            só lugar.
          </p>
          <div className="auth-benefits">
            <div>
              <CalendarDays size={19} />
              <span>
                <strong>Agenda inteligente</strong>
                <small>Atendimentos sem conflito de horários.</small>
              </span>
            </div>
            <div>
              <TrendingUp size={19} />
              <span>
                <strong>Resultados claros</strong>
                <small>Acompanhe receitas, custos e lucro.</small>
              </span>
            </div>
          </div>
        </div>
        <PawPrint className="auth-paw auth-paw--one" />
        <PawPrint className="auth-paw auth-paw--two" />
      </section>

      <section className="auth-form-side">
        <div className="auth-card">
          <div className="auth-mobile-brand">
            <span className="brand-mark">
              <PawPrint size={21} />
            </span>
            <strong>Pet Mania</strong>
          </div>
          <span className="auth-eyebrow">
            {mode === "login" ? "BEM-VINDO(A) DE VOLTA" : "COMECE AGORA"}
          </span>
          <h2>{mode === "login" ? "Acesse sua conta" : "Crie sua conta"}</h2>
          <p>
            {mode === "login"
              ? "Informe seus dados para continuar no sistema."
              : "Cadastre seus dados para acessar a gestão da Pet Mania."}
          </p>

          <form className="auth-form" onSubmit={submit}>
            {mode === "register" && (
              <Field label="Nome completo" required>
                <div className="auth-input">
                  <UserRound size={17} />
                  <input
                    value={authForm.name}
                    onChange={(event) => update("name", event.target.value)}
                    placeholder="Digite seu nome"
                    autoComplete="name"
                  />
                </div>
              </Field>
            )}
            <Field label="E-mail" required>
              <div className="auth-input">
                <Mail size={17} />
                <input
                  type="email"
                  value={authForm.email}
                  onChange={(event) => update("email", event.target.value)}
                  placeholder="voce@petmania.com"
                  autoComplete="email"
                />
              </div>
            </Field>
            <Field label="Senha" required>
              <div className="auth-input">
                <LockKeyhole size={17} />
                <input
                  type={showPassword ? "text" : "password"}
                  value={authForm.password}
                  onChange={(event) => update("password", event.target.value)}
                  placeholder="Mínimo de 6 caracteres"
                  autoComplete={
                    mode === "login" ? "current-password" : "new-password"
                  }
                />
                <button
                  type="button"
                  onClick={() => setShowPassword((current) => !current)}
                  aria-label={showPassword ? "Ocultar senha" : "Mostrar senha"}
                >
                  {showPassword ? <EyeOff size={17} /> : <Eye size={17} />}
                </button>
              </div>
            </Field>
            {mode === "register" && (
              <Field label="Confirmar senha" required>
                <div className="auth-input">
                  <LockKeyhole size={17} />
                  <input
                    type={showPassword ? "text" : "password"}
                    value={authForm.confirmPassword}
                    onChange={(event) =>
                      update("confirmPassword", event.target.value)
                    }
                    placeholder="Digite a senha novamente"
                    autoComplete="new-password"
                  />
                </div>
              </Field>
            )}
            {error && (
              <div className="form-error">
                <AlertTriangle size={16} />
                {error}
              </div>
            )}
            <Button type="submit" className="auth-submit">
              {mode === "login" ? <LogIn size={17} /> : <UserPlus size={17} />}
              {mode === "login" ? "Entrar no sistema" : "Criar conta"}
            </Button>
          </form>

          {mode === "login" && (
            <div className="demo-access">
              <span>ACESSO DE DEMONSTRAÇÃO</span>
              <p>
                <b>admin@petmania.com</b>
                <b>Senha: 123456</b>
              </p>
            </div>
          )}

          <div className="auth-switch">
            {mode === "login"
              ? "Ainda não tem uma conta?"
              : "Já possui uma conta?"}
            <button
              onClick={() =>
                changeMode(mode === "login" ? "register" : "login")
              }
            >
              {mode === "login" ? "Cadastre-se" : "Fazer login"}
            </button>
          </div>
          <small className="auth-note">
            Protótipo demonstrativo: os dados de acesso são armazenados apenas
            neste navegador.
          </small>
        </div>
      </section>
    </main>
  )
}
