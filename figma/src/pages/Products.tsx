import { AlertTriangle, Box, Pencil, Plus, X } from "lucide-react"
import type { OpenModal, Product } from "../types/domain"
import Button from "../components/ui/Button"
import PageHeader from "../components/ui/PageHeader"
import SearchBox from "../components/ui/SearchBox"
import StatusBadge from "../components/ui/StatusBadge"
import { money } from "../utils/formatters"

export default function Products({
  products,
  query,
  setQuery,
  openModal,
  remove,
}: {
  products: Product[]
  query: string
  setQuery: (query: string) => void
  openModal: OpenModal
  remove: (id: number) => void
}) {
  const visible = products.filter((item: Product) =>
    `${item.name} ${item.category}`.toLowerCase().includes(query.toLowerCase()),
  )
  return (
    <>
      <PageHeader
        title="Produtos"
        description="Gerencie catálogo, preços e níveis de estoque."
        action={
          <Button onClick={() => openModal("product")}>
            <Plus size={17} /> Novo produto
          </Button>
        }
      />
      <div className="toolbar">
        <SearchBox
          value={query}
          onChange={setQuery}
          placeholder="Buscar produto..."
        />
        <div className="stock-alert">
          <AlertTriangle size={16} />{" "}
          {products.filter((item: Product) => item.stock <= 5).length} itens com
          estoque baixo
        </div>
      </div>
      <article className="panel table-panel">
        <div className="data-table">
          <div className="table-row table-head">
            <span>Produto</span>
            <span>Categoria</span>
            <span>Estoque</span>
            <span>Preço</span>
            <span>Margem</span>
            <span />
          </div>
          {visible.map((item: Product) => (
            <div className="table-row" key={item.id}>
              <span className="product-name">
                <i>
                  <Box size={18} />
                </i>
                <strong>
                  {item.name}
                  <small>SKU PM-{String(item.id).padStart(4, "0")}</small>
                </strong>
              </span>
              <span>{item.category}</span>
              <span>
                <strong>{item.stock} un.</strong>{" "}
                {item.stock <= 5 && <StatusBadge status="Estoque baixo" />}
              </span>
              <span>{money(item.price)}</span>
              <span className="positive">
                {Math.round(((item.price - item.cost) / item.price) * 100)}%
              </span>
              <span className="row-actions">
                <button>
                  <Pencil size={16} />
                </button>
                <button onClick={() => remove(item.id)}>
                  <X size={16} />
                </button>
              </span>
            </div>
          ))}
        </div>
      </article>
    </>
  )
}
