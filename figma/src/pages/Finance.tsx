import {
  ArrowDownRight,
  ArrowUpRight,
  Plus,
  TrendingUp,
  Users,
} from "lucide-react"
import type { FinancialTotals, OpenModal, Transaction } from "../types/domain"
import Button from "../components/ui/Button"
import PageHeader from "../components/ui/PageHeader"
import StatCard from "../components/ui/StatCard"
import { money, shortDate } from "../utils/formatters"

export default function Finance({
  totals,
  transactions,
  openModal,
}: {
  totals: FinancialTotals
  transactions: Transaction[]
  openModal: OpenModal
}) {
  return (
    <>
      <PageHeader
        title="Financeiro"
        description="Visão clara das receitas, despesas e resultados."
        action={
          <Button onClick={() => openModal("transaction")}>
            <Plus size={17} /> Novo lançamento
          </Button>
        }
      />
      <section className="stats-grid">
        <StatCard
          label="Receitas"
          value={money(totals.revenue)}
          detail="12,4% neste mês"
          icon={ArrowUpRight}
        />
        <StatCard
          label="Custos"
          value={money(totals.costs)}
          detail="6,1% neste mês"
          icon={ArrowDownRight}
          tone="coral"
        />
        <StatCard
          label="Comissões"
          value={money(totals.commissions)}
          detail="equipe de serviços"
          icon={Users}
          tone="gold"
        />
        <StatCard
          label="Lucro líquido"
          value={money(totals.profit)}
          detail={`${Math.round((totals.profit / totals.revenue) * 100)}% de margem`}
          icon={TrendingUp}
          tone="blue"
        />
      </section>
      <section className="overview-grid finance-grid">
        <article className="panel chart-panel">
          <div className="panel-head">
            <div>
              <span>COMPARATIVO</span>
              <h2>Receitas x despesas</h2>
            </div>
            <span className="period-pill">Últimos 6 meses</span>
          </div>
          <div className="finance-bars">
            {[54, 68, 61, 79, 72, 92].map((value, index) => (
              <div key={index}>
                <i style={{ height: `${value}%` }} />
                <b style={{ height: `${value * 0.42}%` }} />
                <span>{["Dez", "Jan", "Fev", "Mar", "Abr", "Mai"][index]}</span>
              </div>
            ))}
          </div>
        </article>
        <article className="panel expense-panel">
          <div className="panel-head">
            <div>
              <span>DESPESAS</span>
              <h2>Composição do período</h2>
            </div>
          </div>
          <div className="donut-wrap">
            <div className="donut">
              <strong>29%</strong>
              <span>da receita</span>
            </div>
            <div className="donut-legend">
              <span>
                <i className="dot dot--coral" /> Insumos{" "}
                <b>{money(totals.costs)}</b>
              </span>
              <span>
                <i className="dot dot--gold" /> Comissões{" "}
                <b>{money(totals.commissions)}</b>
              </span>
            </div>
          </div>
        </article>
      </section>
      <article className="panel table-panel">
        <div className="panel-head padded">
          <div>
            <span>MOVIMENTAÇÕES</span>
            <h2>Lançamentos recentes</h2>
          </div>
        </div>
        <div className="data-table">
          <div className="table-row finance-row table-head">
            <span>Data</span>
            <span>Descrição</span>
            <span>Tipo</span>
            <span>Valor</span>
          </div>
          {transactions.slice(0, 8).map((item: Transaction) => (
            <div className="table-row finance-row" key={item.id}>
              <span>{shortDate(item.date)}</span>
              <span>
                <strong>{item.description}</strong>
              </span>
              <span>
                <span
                  className={`transaction-type transaction-type--${item.type.toLowerCase().replace("ã", "a")}`}
                >
                  {item.type}
                </span>
              </span>
              <span
                className={item.type === "Receita" ? "positive" : "negative"}
              >
                <strong>
                  {item.type === "Receita" ? "+" : "−"} {money(item.value)}
                </strong>
              </span>
            </div>
          ))}
        </div>
      </article>
    </>
  )
}
