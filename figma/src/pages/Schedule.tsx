import {
  CalendarDays,
  Check,
  ChevronLeft,
  ChevronRight,
  PawPrint,
  Plus,
  X,
} from "lucide-react"
import type {
  Appointment,
  Client,
  OpenModal,
  Pet,
  Service,
} from "../types/domain"
import Button from "../components/ui/Button"
import PageHeader from "../components/ui/PageHeader"
import EmptyState from "../components/ui/EmptyState"
import SearchBox from "../components/ui/SearchBox"
import StatusBadge from "../components/ui/StatusBadge"

export default function Schedule({
  appointments,
  date,
  setDate,
  query,
  setQuery,
  statusFilter,
  setStatusFilter,
  openModal,
  clientById,
  petById,
  serviceById,
  completeAppointment,
  cancel,
}: {
  appointments: Appointment[]
  date: string
  setDate: (date: string) => void
  query: string
  setQuery: (query: string) => void
  statusFilter: string
  setStatusFilter: (status: string) => void
  openModal: OpenModal
  clientById: (id: number) => Client | undefined
  petById: (id: number) => Pet | undefined
  serviceById: (id: number) => Service | undefined
  completeAppointment: (appointment: Appointment) => void
  cancel: (id: number) => void
}) {
  const items = appointments
    .filter((item: Appointment) => item.date === date)
    .filter(
      (item: Appointment) =>
        statusFilter === "Todos" || item.status === statusFilter,
    )
    .filter((item: Appointment) =>
      `${petById(item.petId)?.name} ${clientById(item.clientId)?.name}`
        .toLowerCase()
        .includes(query.toLowerCase()),
    )
    .sort((a: Appointment, b: Appointment) => a.time.localeCompare(b.time))
  return (
    <>
      <PageHeader
        title="Agenda"
        description="Acompanhe e organize todos os atendimentos."
        action={
          <Button onClick={() => openModal("appointment", { date })}>
            <Plus size={17} /> Novo agendamento
          </Button>
        }
      />
      <div className="toolbar">
        <SearchBox
          value={query}
          onChange={setQuery}
          placeholder="Buscar pet ou tutor..."
        />
        <div className="segmented">
          {["Todos", "Agendado", "Em andamento", "Concluído"].map((status) => (
            <button
              className={statusFilter === status ? "active" : ""}
              onClick={() => setStatusFilter(status)}
              key={status}
            >
              {status}
            </button>
          ))}
        </div>
      </div>
      <article className="panel schedule-panel">
        <div className="date-nav">
          <button className="icon-button">
            <ChevronLeft size={19} />
          </button>
          <label>
            <CalendarDays size={17} />
            <input
              type="date"
              value={date}
              onChange={(event) => setDate(event.target.value)}
            />
          </label>
          <button className="icon-button">
            <ChevronRight size={19} />
          </button>
          <span>{items.length} atendimento(s)</span>
        </div>
        {items.length ? (
          <div className="appointment-list">
            {items.map((item: Appointment) => (
              <div className="appointment-card" key={item.id}>
                <div className="appointment-time">
                  <strong>{item.time}</strong>
                  <span>{serviceById(item.serviceId)?.duration} min</span>
                </div>
                <div className="appointment-line" />
                <div className="pet-avatar pet-avatar--large">
                  <PawPrint size={20} />
                </div>
                <div className="appointment-main">
                  <strong>
                    {petById(item.petId)?.name}
                    <small>{petById(item.petId)?.breed}</small>
                  </strong>
                  <span>
                    {clientById(item.clientId)?.name} ·{" "}
                    {serviceById(item.serviceId)?.name}
                  </span>
                </div>
                <div className="professional">
                  <span className="avatar avatar--small">
                    {item.professional.slice(0, 2).toUpperCase()}
                  </span>
                  <span>{item.professional}</span>
                </div>
                <StatusBadge status={item.status} />
                <div className="row-actions">
                  {(item.status === "Agendado" ||
                    item.status === "Em andamento") && (
                    <button
                      title="Concluir"
                      onClick={() => completeAppointment(item)}
                    >
                      <Check size={16} />
                    </button>
                  )}
                  {item.status === "Agendado" && (
                    <button title="Cancelar" onClick={() => cancel(item.id)}>
                      <X size={16} />
                    </button>
                  )}
                </div>
              </div>
            ))}
          </div>
        ) : (
          <EmptyState text="Tente mudar a data ou os filtros da agenda." />
        )}
      </article>
    </>
  )
}
