import { CircleDollarSign, Package, Plus, ShoppingBag } from "lucide-react"
import type { OpenModal, Product, Sale } from "../types/domain"
import Button from "../components/ui/Button"
import PageHeader from "../components/ui/PageHeader"
import StatCard from "../components/ui/StatCard"
import { money, shortDate } from "../utils/formatters"

export default function Sales({
  sales,
  productById,
  openModal,
}: {
  sales: Sale[]
  productById: (id: number) => Product | undefined
  openModal: OpenModal
}) {
  const salesTotal = sales.reduce(
    (sum: number, item: Sale) => sum + item.total,
    0,
  )
  return (
    <>
      <PageHeader
        title="Vendas"
        description="Registre e acompanhe as vendas de produtos."
        action={
          <Button onClick={() => openModal("sale")}>
            <Plus size={17} /> Nova venda
          </Button>
        }
      />
      <section className="stats-grid stats-grid--three">
        <StatCard
          label="Vendas registradas"
          value={`${sales.length}`}
          detail="3 novas nesta semana"
          icon={ShoppingBag}
        />
        <StatCard
          label="Receita em produtos"
          value={money(salesTotal)}
          detail="9,3% neste mês"
          icon={CircleDollarSign}
          tone="blue"
        />
        <StatCard
          label="Itens vendidos"
          value={`${sales.reduce((sum: number, item: Sale) => sum + item.quantity, 0)}`}
          detail="giro saudável"
          icon={Package}
          tone="coral"
        />
      </section>
      <article className="panel table-panel">
        <div className="panel-head padded">
          <div>
            <span>HISTÓRICO</span>
            <h2>Vendas recentes</h2>
          </div>
        </div>
        <div className="data-table">
          <div className="table-row sales-row table-head">
            <span>Data</span>
            <span>Produto</span>
            <span>Cliente</span>
            <span>Quantidade</span>
            <span>Total</span>
          </div>
          {sales.map((item: Sale) => (
            <div className="table-row sales-row" key={item.id}>
              <span>{shortDate(item.date)}</span>
              <span className="product-name">
                <i>
                  <ShoppingBag size={17} />
                </i>
                <strong>{productById(item.productId)?.name}</strong>
              </span>
              <span>{item.client}</span>
              <span>{item.quantity} un.</span>
              <span className="positive">
                <strong>{money(item.total)}</strong>
              </span>
            </div>
          ))}
        </div>
      </article>
    </>
  )
}
