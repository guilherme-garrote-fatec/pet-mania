import { Bath, Clock3, Pencil, Plus, Scissors } from "lucide-react"
import type { OpenModal, Service } from "../types/domain"
import Button from "../components/ui/Button"
import PageHeader from "../components/ui/PageHeader"
import StatusBadge from "../components/ui/StatusBadge"
import { money } from "../utils/formatters"

export default function Services({
  services,
  openModal,
  toggle,
}: {
  services: Service[]
  openModal: OpenModal
  toggle: (id: number) => void
}) {
  return (
    <>
      <PageHeader
        title="Serviços"
        description="Configure os cuidados oferecidos pela Pet Mania."
        action={
          <Button onClick={() => openModal("service")}>
            <Plus size={17} /> Novo serviço
          </Button>
        }
      />
      <div className="service-grid">
        {services.map((item: Service, index: number) => {
          const Icon = index % 2 ? Scissors : Bath
          return (
            <article
              className={`service-card ${
                !item.active ? "service-card--inactive" : ""
              }`}
              key={item.id}
            >
              <div className="service-card-head">
                <span>
                  <Icon />
                </span>
                <StatusBadge status={item.active ? "Ativo" : "Inativo"} />
              </div>
              <h2>{item.name}</h2>
              <p>
                Cuidado completo com produtos selecionados e equipe
                especializada.
              </p>
              <div className="service-price">
                <strong>{money(item.price)}</strong>
                <span>
                  <Clock3 size={15} /> {item.duration} min
                </span>
              </div>
              <div className="service-metrics">
                <div>
                  <span>Custo estimado</span>
                  <strong>{money(item.cost)}</strong>
                </div>
                <div>
                  <span>Margem</span>
                  <strong>
                    {Math.round(((item.price - item.cost) / item.price) * 100)}%
                  </strong>
                </div>
              </div>
              <div className="service-actions">
                <Button variant="secondary">
                  <Pencil size={15} /> Editar
                </Button>
                <button onClick={() => toggle(item.id)}>
                  {item.active ? "Desativar" : "Ativar"}
                </button>
              </div>
            </article>
          )
        })}
      </div>
    </>
  )
}
