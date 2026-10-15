import {
  Bath,
  CalendarDays,
  Check,
  ChevronRight,
  CircleDollarSign,
  PawPrint,
  Plus,
  ReceiptText,
  ShoppingBag,
  TrendingUp,
  UserRound,
} from "lucide-react"
import type {
  Appointment,
  Client,
  FinancialTotals,
  OpenModal,
  Page,
  Pet,
  Service,
} from "../types/domain"
import Button from "../components/ui/Button"
import PageHeader from "../components/ui/PageHeader"
import StatCard from "../components/ui/StatCard"
import StatusBadge from "../components/ui/StatusBadge"
import { money } from "../utils/formatters"
import { today } from "../data/mockData"

export default function Overview({
  appointments,
  totals,
  openModal,
  clientById,
  petById,
  serviceById,
  setPage,
  completeAppointment,
}: {
  appointments: Appointment[]
  totals: FinancialTotals
  openModal: OpenModal
  clientById: (id: number) => Client | undefined
  petById: (id: number) => Pet | undefined
  serviceById: (id: number) => Service | undefined
  setPage: (page: Page) => void
  completeAppointment: (appointment: Appointment) => void
}) {
  const dayAppointments = appointments.filter(
    (item: Appointment) => item.date === today && item.status !== "Cancelado",
  )
  return (
    <>
      <PageHeader
        eyebrow="SEGUNDA-FEIRA, 18 DE MAIO"
        title="Bom dia, Sônia!"
        description="A Pet Mania está com tudo organizado para hoje."
        action={
          <Button onClick={() => openModal("appointment", { date: today })}>
            <Plus size={17} /> Novo agendamento
          </Button>
        }
      />
      <section className="stats-grid">
        <StatCard
          label="Faturamento"
          value={money(totals.revenue)}
          detail="12,4% neste mês"
          icon={CircleDollarSign}
        />
        <StatCard
          label="Lucro líquido"
          value={money(totals.profit)}
          detail="8,2% neste mês"
          icon={TrendingUp}
          tone="blue"
        />
        <StatCard
          label="Atendimentos hoje"
          value={`${dayAppointments.length}`}
          detail={`${dayAppointments.filter((item: Appointment) => item.status === "Concluído").length} concluído(s)`}
          icon={Bath}
          tone="coral"
        />
        <StatCard
          label="Ticket médio"
          value={money(totals.revenue / 14)}
          detail="5,7% neste mês"
          icon={ReceiptText}
          tone="gold"
        />
      </section>
      <section className="overview-grid">
        <article className="panel chart-panel">
          <div className="panel-head">
            <div>
              <span>DESEMPENHO</span>
              <h2>Visão financeira</h2>
            </div>
            <button className="period-pill">
              Últimos 6 meses <ChevronRight size={14} />
            </button>
          </div>
          <div className="chart-legend">
            <span>
              <i className="dot dot--green" /> Receita
            </span>
            <span>
              <i className="dot dot--coral" /> Despesas
            </span>
          </div>
          <div className="bar-chart">
            {[
              ["Dez", 46, 24],
              ["Jan", 58, 31],
              ["Fev", 51, 27],
              ["Mar", 70, 36],
              ["Abr", 66, 30],
              ["Mai", 88, 39],
            ].map(([month, revenue, cost]) => (
              <div className="bar-group" key={month as string}>
                <div className="bars">
                  <i style={{ height: `${revenue}%` }} />
                  <i style={{ height: `${cost}%` }} />
                </div>
                <span>{month}</span>
              </div>
            ))}
          </div>
        </article>
        <article className="panel">
          <div className="panel-head">
            <div>
              <span>HOJE</span>
              <h2>Próximos atendimentos</h2>
            </div>
            <button className="text-button" onClick={() => setPage("schedule")}>
              Ver agenda <ChevronRight size={15} />
            </button>
          </div>
          <div className="timeline">
            {dayAppointments.slice(0, 4).map((item: Appointment) => (
              <div className="timeline-item" key={item.id}>
                <time>{item.time}</time>
                <div className="pet-avatar">
                  <PawPrint size={17} />
                </div>
                <div className="timeline-copy">
                  <strong>{petById(item.petId)?.name}</strong>
                  <span>
                    {serviceById(item.serviceId)?.name} ·{" "}
                    {clientById(item.clientId)?.name}
                  </span>
                </div>
                <StatusBadge status={item.status} />
                {item.status === "Em andamento" && (
                  <button
                    className="mini-action"
                    onClick={() => completeAppointment(item)}
                  >
                    <Check size={14} />
                  </button>
                )}
              </div>
            ))}
          </div>
        </article>
      </section>
      <section className="bottom-grid">
        <article className="panel">
          <div className="panel-head">
            <div>
              <span>ATALHOS</span>
              <h2>Ações rápidas</h2>
            </div>
          </div>
          <div className="quick-actions">
            <button onClick={() => openModal("appointment", { date: today })}>
              <span>
                <CalendarDays />
              </span>
              <strong>Agendamento</strong>
              <small>Reservar horário</small>
            </button>
            <button onClick={() => openModal("sale")}>
              <span>
                <ShoppingBag />
              </span>
              <strong>Nova venda</strong>
              <small>Registrar produto</small>
            </button>
            <button onClick={() => openModal("client")}>
              <span>
                <UserRound />
              </span>
              <strong>Novo cliente</strong>
              <small>Cadastrar tutor</small>
            </button>
          </div>
        </article>
        <article className="panel activity-panel">
          <div className="panel-head">
            <div>
              <span>MOVIMENTAÇÕES</span>
              <h2>Atividade recente</h2>
            </div>
          </div>
          <div className="activity-list">
            <div>
              <span className="activity-icon green">
                <Check />
              </span>
              <p>
                <strong>Atendimento concluído</strong>
                <small>Banho completo · Bento</small>
              </p>
              <time>há 12 min</time>
            </div>
            <div>
              <span className="activity-icon coral">
                <ShoppingBag />
              </span>
              <p>
                <strong>Venda registrada</strong>
                <small>Coleira Comfort · {money(49.9)}</small>
              </p>
              <time>há 35 min</time>
            </div>
            <div>
              <span className="activity-icon blue">
                <UserRound />
              </span>
              <p>
                <strong>Novo cliente</strong>
                <small>Lucas Almeida e Max</small>
              </p>
              <time>ontem</time>
            </div>
          </div>
        </article>
      </section>
    </>
  )
}
