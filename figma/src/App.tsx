import { useMemo, useState, type FormEvent } from "react"
import {
  Bell,
  CalendarDays,
  Check,
  FileBarChart,
  Home,
  LogOut,
  Menu,
  Package,
  PawPrint,
  Scissors,
  ShoppingBag,
  Users,
  WalletCards,
} from "lucide-react"
import AuthScreen from "./components/auth/AuthScreen"
import Modal from "./components/ui/Modal"
import PrototypeForm, { modalTitles } from "./features/forms/PrototypeForm"
import Overview from "./pages/Overview"
import Schedule from "./pages/Schedule"
import Clients from "./pages/Clients"
import Products from "./pages/Products"
import Services from "./pages/Services"
import Sales from "./pages/Sales"
import Finance from "./pages/Finance"
import Reports from "./pages/Reports"
import {
  initialAppointments,
  initialClients,
  initialProducts,
  initialSales,
  initialServices,
  initialTransactions,
  today,
} from "./data/mockData"
import type {
  AppUser,
  Appointment,
  ModalType,
  Page,
  Transaction,
} from "./types/domain"

const navItems: {
  id: Page
  label: string
  icon: typeof Home
}[] = [
  { id: "overview", label: "Visão geral", icon: Home },
  { id: "schedule", label: "Agenda", icon: CalendarDays },
  { id: "clients", label: "Clientes & Pets", icon: Users },
  { id: "products", label: "Produtos", icon: Package },
  { id: "services", label: "Serviços", icon: Scissors },
  { id: "sales", label: "Vendas", icon: ShoppingBag },
  { id: "finance", label: "Financeiro", icon: WalletCards },
  { id: "reports", label: "Relatórios", icon: FileBarChart },
]

export default function App() {
  const [currentUser, setCurrentUser] = useState<AppUser | null>(() => {
    const storedSession = localStorage.getItem("pet-mania-session")
    return storedSession ? JSON.parse(storedSession) as AppUser : null
  })
  const [page, setPage] = useState<Page>("overview")
  const [mobileMenu, setMobileMenu] = useState(false)
  const [modal, setModal] = useState<ModalType>(null)
  const [toast, setToast] = useState("")
  const [query, setQuery] = useState("")
  const [statusFilter, setStatusFilter] = useState("Todos")
  const [clients, setClients] = useState(initialClients)
  const [services, setServices] = useState(initialServices)
  const [products, setProducts] = useState(initialProducts)
  const [appointments, setAppointments] = useState(initialAppointments)
  const [transactions, setTransactions] = useState(initialTransactions)
  const [sales, setSales] = useState(initialSales)
  const [form, setForm] = useState<Record<string, string>>({})
  const [formError, setFormError] = useState("")
  const [selectedClient, setSelectedClient] = useState<number>(1)
  const [scheduleDate, setScheduleDate] = useState(today)

  const totals = useMemo(() => {
    const revenue = transactions
      .filter((item) => item.type === "Receita")
      .reduce((sum, item) => sum + item.value, 0)
    const costs = transactions
      .filter((item) => item.type === "Custo")
      .reduce((sum, item) => sum + item.value, 0)
    const commissions = transactions
      .filter((item) => item.type === "Comissão")
      .reduce((sum, item) => sum + item.value, 0)
    return {
      revenue,
      costs,
      commissions,
      profit: revenue - costs - commissions,
    }
  }, [transactions])

  const notify = (message: string) => {
    setToast(message)
    window.setTimeout(() => setToast(""), 2800)
  }
  const openModal = (
    type: ModalType,
    defaults: Record<string, string> = {},
  ) => {
    setForm(defaults)
    setFormError("")
    setModal(type)
  }
  const updateForm = (key: string, value: string) =>
    setForm((current) => ({ ...current, [key]: value }))
  const clientById = (id: number) => clients.find((client) => client.id === id)
  const petById = (id: number) =>
    clients.flatMap((client) => client.pets).find((pet) => pet.id === id)
  const serviceById = (id: number) =>
    services.find((service) => service.id === id)
  const productById = (id: number) =>
    products.find((product) => product.id === id)

  const changePage = (next: Page) => {
    setPage(next)
    setQuery("")
    setMobileMenu(false)
  }

  const completeAppointment = (appointment: Appointment) => {
    const service = serviceById(appointment.serviceId)
    const pet = petById(appointment.petId)
    if (!service || appointment.status === "Concluído") return
    setAppointments((items) =>
      items.map((item) =>
        item.id === appointment.id ? { ...item, status: "Concluído" } : item,
      ),
    )
    const base = Date.now()
    setTransactions((items) => [
      {
        id: base,
        date: appointment.date,
        description: `${service.name} · ${pet?.name}`,
        type: "Receita",
        value: service.price,
      },
      {
        id: base + 1,
        date: appointment.date,
        description: `Insumos · ${pet?.name}`,
        type: "Custo",
        value: service.cost,
      },
      {
        id: base + 2,
        date: appointment.date,
        description: `Comissão · ${appointment.professional}`,
        type: "Comissão",
        value: service.price * 0.15,
      },
      ...items,
    ])
    notify("Atendimento concluído e financeiro atualizado.")
  }

  const submitForm = (event: FormEvent) => {
    event.preventDefault()
    setFormError("")
    const id = Date.now()
    if (modal === "appointment") {
      const clientId = Number(form.clientId)
      const client = clientById(clientId)
      if (
        !clientId ||
        !form.petId ||
        !form.serviceId ||
        !form.date ||
        !form.time
      )
        return setFormError("Preencha todos os campos obrigatórios.")
      if (
        appointments.some(
          (item) =>
            item.date === form.date &&
            item.time === form.time &&
            item.status !== "Cancelado",
        )
      ) {
        return setFormError(
          "Este horário já está ocupado. Escolha outro horário.",
        )
      }
      setAppointments((items) => [
        ...items,
        {
          id,
          date: form.date,
          time: form.time,
          clientId,
          petId: Number(form.petId),
          serviceId: Number(form.serviceId),
          professional: form.professional || "Eliane",
          status: "Agendado",
        },
      ])
      setScheduleDate(form.date)
      notify(`Agendamento criado para ${client?.name}.`)
    }
    if (modal === "client") {
      if (!form.name || !form.phone || !form.petName || !form.breed)
        return setFormError("Preencha os dados do tutor e do pet.")
      setClients((items) => [
        ...items,
        {
          id,
          name: form.name,
          phone: form.phone,
          email: form.email || "Não informado",
          pets: [
            {
              id: id + 1,
              name: form.petName,
              breed: form.breed,
              sex: form.sex || "Não informado",
            },
          ],
        },
      ])
      notify("Cliente e pet cadastrados com sucesso.")
    }
    if (modal === "product") {
      if (
        !form.name ||
        !form.category ||
        !form.price ||
        !form.cost ||
        !form.stock
      )
        return setFormError("Preencha todos os campos do produto.")
      setProducts((items) => [
        ...items,
        {
          id,
          name: form.name,
          category: form.category,
          price: Number(form.price),
          cost: Number(form.cost),
          stock: Number(form.stock),
        },
      ])
      notify("Produto adicionado ao catálogo.")
    }
    if (modal === "service") {
      if (!form.name || !form.price || !form.cost || !form.duration)
        return setFormError("Preencha todos os campos do serviço.")
      setServices((items) => [
        ...items,
        {
          id,
          name: form.name,
          price: Number(form.price),
          cost: Number(form.cost),
          duration: Number(form.duration),
          active: true,
        },
      ])
      notify("Serviço cadastrado com sucesso.")
    }
    if (modal === "sale") {
      const product = productById(Number(form.productId))
      const quantity = Number(form.quantity)
      if (!product || !quantity)
        return setFormError("Selecione o produto e informe a quantidade.")
      if (quantity > product.stock)
        return setFormError(
          `Estoque insuficiente. Disponível: ${product.stock} unidade(s).`,
        )
      const total = product.price * quantity
      setProducts((items) =>
        items.map((item) =>
          item.id === product.id
            ? { ...item, stock: item.stock - quantity }
            : item,
        ),
      )
      setSales((items) => [
        {
          id,
          date: today,
          productId: product.id,
          quantity,
          client: form.client || "Venda balcão",
          total,
        },
        ...items,
      ])
      setTransactions((items) => [
        {
          id,
          date: today,
          description: `Venda · ${product.name}`,
          type: "Receita",
          value: total,
        },
        {
          id: id + 1,
          date: today,
          description: `CMV · ${product.name}`,
          type: "Custo",
          value: product.cost * quantity,
        },
        ...items,
      ])
      notify("Venda registrada e estoque atualizado.")
    }
    if (modal === "transaction") {
      if (!form.description || !form.value || !form.type)
        return setFormError("Preencha todos os campos do lançamento.")
      setTransactions((items) => [
        {
          id,
          date: form.date || today,
          description: form.description,
          type: form.type as Transaction["type"],
          value: Number(form.value),
        },
        ...items,
      ])
      notify("Lançamento financeiro registrado.")
    }
    setModal(null)
  }

  const pageTitle =
    navItems.find((item) => item.id === page)?.label || "Visão geral"
  const userInitials = currentUser?.name
    .split(" ")
    .map((part) => part[0])
    .slice(0, 2)
    .join("")
    .toUpperCase()

  const authenticate = (user: AppUser) => {
    localStorage.setItem("pet-mania-session", JSON.stringify(user))
    setCurrentUser(user)
  }

  const logOut = () => {
    localStorage.removeItem("pet-mania-session")
    setCurrentUser(null)
    setPage("overview")
    setMobileMenu(false)
  }

  if (!currentUser) return <AuthScreen onAuthenticate={authenticate} />

  return (
    <div className="app">
      <aside className={`sidebar ${mobileMenu ? "sidebar--open" : ""}`}>
        <div className="brand">
          <span className="brand-mark">
            <PawPrint size={22} />
          </span>
          <span>
            Pet <b>Mania</b>
          </span>
        </div>
        <nav className="nav">
          <span className="nav-label">MENU PRINCIPAL</span>
          {navItems.map((item) => {
            const Icon = item.icon
            return (
              <button
                key={item.id}
                className={`nav-item ${
                  page === item.id ? "nav-item--active" : ""
                }`}
                onClick={() => changePage(item.id)}
              >
                <Icon size={19} />
                <span>{item.label}</span>
              </button>
            )
          })}
        </nav>
        <div className="sidebar-help">
          <span>
            <PawPrint size={18} />
          </span>
          <strong>Precisa de ajuda?</strong>
          <p>Acesse o guia rápido do sistema.</p>
          <button onClick={() => notify("Guia rápido disponível em breve.")}>
            Ver guia rápido
          </button>
        </div>
        <div className="profile">
          <div className="avatar">{userInitials}</div>
          <div>
            <strong>{currentUser.name}</strong>
            <span>{currentUser.role}</span>
          </div>
          <button
            className="icon-button logout-button"
            onClick={logOut}
            aria-label="Sair do sistema"
            title="Sair"
          >
            <LogOut size={17} />
          </button>
        </div>
      </aside>
      {mobileMenu && (
        <div className="menu-backdrop" onClick={() => setMobileMenu(false)} />
      )}

      <main className="main">
        <header className="topbar">
          <button
            className="icon-button mobile-only"
            onClick={() => setMobileMenu(true)}
            aria-label="Abrir menu"
          >
            <Menu size={22} />
          </button>
          <div className="topbar-title">
            <span>Pet Mania</span>
            <strong>{pageTitle}</strong>
          </div>
          <div className="topbar-actions">
            <button className="icon-button notification">
              <Bell size={19} />
              <i />
            </button>
            <div className="topbar-avatar">{userInitials}</div>
          </div>
        </header>

        <div className="content">
          {page === "overview" && (
            <Overview
              appointments={appointments}
              totals={totals}
              openModal={openModal}
              clientById={clientById}
              petById={petById}
              serviceById={serviceById}
              setPage={changePage}
              completeAppointment={completeAppointment}
            />
          )}
          {page === "schedule" && (
            <Schedule
              appointments={appointments}
              date={scheduleDate}
              setDate={setScheduleDate}
              query={query}
              setQuery={setQuery}
              statusFilter={statusFilter}
              setStatusFilter={setStatusFilter}
              openModal={openModal}
              clientById={clientById}
              petById={petById}
              serviceById={serviceById}
              completeAppointment={completeAppointment}
              cancel={(id) => {
                setAppointments((items) =>
                  items.map((item) =>
                    item.id === id ? { ...item, status: "Cancelado" } : item,
                  ),
                )
                notify("Agendamento cancelado.")
              }}
            />
          )}
          {page === "clients" && (
            <Clients
              clients={clients}
              query={query}
              setQuery={setQuery}
              selected={selectedClient}
              setSelected={setSelectedClient}
              openModal={openModal}
              appointments={appointments}
              serviceById={serviceById}
            />
          )}
          {page === "products" && (
            <Products
              products={products}
              query={query}
              setQuery={setQuery}
              openModal={openModal}
              remove={(id) => {
                setProducts((items) => items.filter((item) => item.id !== id))
                notify("Produto removido.")
              }}
            />
          )}
          {page === "services" && (
            <Services
              services={services}
              openModal={openModal}
              toggle={(id) =>
                setServices((items) =>
                  items.map((item) =>
                    item.id === id ? { ...item, active: !item.active } : item,
                  ),
                )
              }
            />
          )}
          {page === "sales" && (
            <Sales
              sales={sales}
              productById={productById}
              openModal={openModal}
            />
          )}
          {page === "finance" && (
            <Finance
              totals={totals}
              transactions={transactions}
              openModal={openModal}
            />
          )}
          {page === "reports" && (
            <Reports
              services={services}
              appointments={appointments}
              notify={notify}
            />
          )}
        </div>
      </main>
      {modal && (
        <Modal
          title={modalTitles[modal][0]}
          subtitle={modalTitles[modal][1]}
          onClose={() => setModal(null)}
        >
          <PrototypeForm
            type={modal}
            form={form}
            update={updateForm}
            submit={submitForm}
            error={formError}
            clients={clients}
            services={services}
            products={products}
            close={() => setModal(null)}
          />
        </Modal>
      )}
      {toast && (
        <div className="toast">
          <span>
            <Check size={16} />
          </span>
          {toast}
        </div>
      )}
    </div>
  )
}
