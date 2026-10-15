import { ArrowUpRight, Download } from "lucide-react"
import type { Appointment, Service } from "../types/domain"
import Button from "../components/ui/Button"
import PageHeader from "../components/ui/PageHeader"
import { money } from "../utils/formatters"

export default function Reports({
  services,
  appointments,
  notify,
}: {
  services: Service[]
  appointments: Appointment[]
  notify: (message: string) => void
}) {
  const completed = appointments.filter(
    (item: Appointment) => item.status === "Concluído",
  )
  return (
    <>
      <PageHeader
        title="Relatórios"
        description="Dados consolidados para decisões mais inteligentes."
        action={
          <Button
            onClick={() =>
              notify("Relatório preparado para exportação (demonstração).")
            }
          >
            <Download size={17} /> Exportar relatório
          </Button>
        }
      />
      <div className="report-filters">
        <label>
          <span>Período</span>
          <select>
            <option>Este mês</option>
            <option>Últimos 3 meses</option>
            <option>Este ano</option>
          </select>
        </label>
        <label>
          <span>Tipo de relatório</span>
          <select>
            <option>Visão completa</option>
            <option>Somente receitas</option>
            <option>Somente custos</option>
          </select>
        </label>
        <Button variant="secondary">Aplicar filtros</Button>
      </div>
      <section className="report-grid">
        <article className="panel">
          <div className="panel-head">
            <div>
              <span>DESEMPENHO</span>
              <h2>Serviços mais realizados</h2>
            </div>
          </div>
          <div className="ranking">
            {services.map((service: Service, index: number) => {
              const count = appointments.filter(
                (item: Appointment) => item.serviceId === service.id,
              ).length
              return (
                <div key={service.id}>
                  <b>{index + 1}</b>
                  <span>
                    <strong>{service.name}</strong>
                    <small>{count || index + 2} atendimento(s)</small>
                  </span>
                  <i>
                    <em style={{ width: `${86 - index * 14}%` }} />
                  </i>
                  <strong>{money(service.price * (count || index + 2))}</strong>
                </div>
              )
            })}
          </div>
        </article>
        <article className="panel report-summary">
          <div className="panel-head">
            <div>
              <span>RESUMO</span>
              <h2>Indicadores do período</h2>
            </div>
          </div>
          <div className="summary-number">
            <span>Taxa de conclusão</span>
            <strong>
              {Math.round((completed.length / appointments.length) * 100)}%
            </strong>
            <small>
              <ArrowUpRight size={14} /> dentro da meta
            </small>
          </div>
          <div className="summary-grid">
            <div>
              <span>Novos clientes</span>
              <strong>12</strong>
            </div>
            <div>
              <span>Pets atendidos</span>
              <strong>38</strong>
            </div>
            <div>
              <span>Cancelamentos</span>
              <strong>3</strong>
            </div>
            <div>
              <span>Ocupação</span>
              <strong>78%</strong>
            </div>
          </div>
        </article>
      </section>
      <article className="panel table-panel">
        <div className="panel-head padded">
          <div>
            <span>ANÁLISE</span>
            <h2>Resultado por serviço</h2>
          </div>
        </div>
        <div className="data-table">
          <div className="table-row report-row table-head">
            <span>Serviço</span>
            <span>Atendimentos</span>
            <span>Receita estimada</span>
            <span>Custo estimado</span>
            <span>Margem</span>
          </div>
          {services.map((item: Service, index: number) => {
            const count =
              appointments.filter(
                (appointment: Appointment) => appointment.serviceId === item.id,
              ).length || index + 2
            return (
              <div className="table-row report-row" key={item.id}>
                <span>
                  <strong>{item.name}</strong>
                </span>
                <span>{count}</span>
                <span>{money(item.price * count)}</span>
                <span>{money(item.cost * count)}</span>
                <span className="positive">
                  <strong>
                    {Math.round(((item.price - item.cost) / item.price) * 100)}%
                  </strong>
                </span>
              </div>
            )
          })}
        </div>
      </article>
    </>
  )
}
