import { Bath, ChevronRight, PawPrint, Pencil, Plus } from "lucide-react"
import type {
  Appointment,
  Client,
  OpenModal,
  Pet,
  Service,
} from "../types/domain"
import Button from "../components/ui/Button"
import PageHeader from "../components/ui/PageHeader"
import SearchBox from "../components/ui/SearchBox"
import StatusBadge from "../components/ui/StatusBadge"
import { shortDate } from "../utils/formatters"

export default function Clients({
  clients,
  query,
  setQuery,
  selected,
  setSelected,
  openModal,
  appointments,
  serviceById,
}: {
  clients: Client[]
  query: string
  setQuery: (query: string) => void
  selected: number
  setSelected: (id: number) => void
  openModal: OpenModal
  appointments: Appointment[]
  serviceById: (id: number) => Service | undefined
}) {
  const visible = clients.filter((client: Client) =>
    `${client.name} ${client.pets.map((pet) => pet.name).join(" ")}`
      .toLowerCase()
      .includes(query.toLowerCase()),
  )
  const active =
    clients.find((client: Client) => client.id === selected) || visible[0]
  const history = active
    ? appointments.filter((item: Appointment) => item.clientId === active.id)
    : []
  return (
    <>
      <PageHeader
        title="Clientes & Pets"
        description={`${clients.length} tutores e ${clients.flatMap((item: Client) => item.pets).length} pets cadastrados.`}
        action={
          <Button onClick={() => openModal("client")}>
            <Plus size={17} /> Novo cliente
          </Button>
        }
      />
      <div className="clients-layout">
        <article className="panel client-list-panel">
          <SearchBox
            value={query}
            onChange={setQuery}
            placeholder="Buscar cliente ou pet..."
          />
          <div className="client-list">
            {visible.map((client: Client) => (
              <button
                className={active?.id === client.id ? "active" : ""}
                onClick={() => setSelected(client.id)}
                key={client.id}
              >
                <span className="avatar">
                  {client.name
                    .split(" ")
                    .map((p) => p[0])
                    .slice(0, 2)
                    .join("")}
                </span>
                <p>
                  <strong>{client.name}</strong>
                  <small>{client.pets.map((pet) => pet.name).join(", ")}</small>
                </p>
                <ChevronRight size={17} />
              </button>
            ))}
          </div>
        </article>
        {active && (
          <article className="panel client-detail">
            <div className="client-hero">
              <span className="avatar avatar--large">
                {active.name
                  .split(" ")
                  .map((p: string) => p[0])
                  .slice(0, 2)
                  .join("")}
              </span>
              <div>
                <span>CLIENTE DESDE 2025</span>
                <h2>{active.name}</h2>
                <p>
                  {active.phone} · {active.email}
                </p>
              </div>
              <button className="icon-button">
                <Pencil size={17} />
              </button>
            </div>
            <div className="section-title">
              <div>
                <span>PETS</span>
                <strong>Companheiros cadastrados</strong>
              </div>
              <button>
                <Plus size={15} /> Adicionar pet
              </button>
            </div>
            <div className="pet-cards">
              {active.pets.map((pet: Pet) => (
                <div key={pet.id}>
                  <span className="pet-avatar pet-avatar--large">
                    <PawPrint />
                  </span>
                  <p>
                    <strong>{pet.name}</strong>
                    <small>
                      {pet.breed} · {pet.sex}
                    </small>
                  </p>
                </div>
              ))}
            </div>
            <div className="section-title">
              <div>
                <span>HISTÓRICO</span>
                <strong>Últimos atendimentos</strong>
              </div>
            </div>
            <div className="simple-list">
              {history.length ? (
                history.map((item: Appointment) => (
                  <div key={item.id}>
                    <span>
                      <Bath size={17} />
                    </span>
                    <p>
                      <strong>{serviceById(item.serviceId)?.name}</strong>
                      <small>
                        {shortDate(item.date)} · {item.time}
                      </small>
                    </p>
                    <StatusBadge status={item.status} />
                  </div>
                ))
              ) : (
                <p className="muted">Nenhum atendimento registrado.</p>
              )}
            </div>
          </article>
        )}
      </div>
    </>
  )
}
