import type { FormEvent } from "react"
import { AlertTriangle, Check } from "lucide-react"
import { today } from "../../data/mockData"
import type {
  Client,
  FormValues,
  ModalType,
  Pet,
  Product,
  Service,
} from "../../types/domain"
import { money } from "../../utils/formatters"
import Button from "../../components/ui/Button"
import Field from "../../components/ui/Field"

export const modalTitles: Record<Exclude<ModalType, null>, [string, string]> = {
  appointment: ["Novo agendamento", "Organize o próximo cuidado do pet."],
  client: ["Novo cliente e pet", "Cadastre o tutor e seu primeiro pet."],
  product: ["Novo produto", "Adicione um item ao catálogo da loja."],
  service: ["Novo serviço", "Defina preço, custo e duração média."],
  sale: ["Registrar venda", "A baixa no estoque será feita automaticamente."],
  transaction: ["Novo lançamento", "Registre uma receita, custo ou comissão."],
}

export default function PrototypeForm({
  type,
  form,
  update,
  submit,
  error,
  clients,
  services,
  products,
  close,
}: {
  type: Exclude<ModalType, null>
  form: FormValues
  update: (key: string, value: string) => void
  submit: (event: FormEvent) => void
  error: string
  clients: Client[]
  services: Service[]
  products: Product[]
  close: () => void
}) {
  const selected = clients.find(
    (client: Client) => client.id === Number(form.clientId),
  )
  return (
    <form className="prototype-form" onSubmit={submit}>
      {type === "appointment" && (
        <>
          <div className="form-grid">
            <Field label="Cliente" required>
              <select
                value={form.clientId || ""}
                onChange={(e) => {
                  update("clientId", e.target.value)
                  update("petId", "")
                }}
              >
                <option value="">Selecione</option>
                {clients.map((item: Client) => (
                  <option value={item.id} key={item.id}>
                    {item.name}
                  </option>
                ))}
              </select>
            </Field>
            <Field label="Pet" required>
              <select
                value={form.petId || ""}
                onChange={(e) => update("petId", e.target.value)}
                disabled={!selected}
              >
                <option value="">Selecione</option>
                {selected?.pets.map((pet: Pet) => (
                  <option value={pet.id} key={pet.id}>
                    {pet.name}
                  </option>
                ))}
              </select>
            </Field>
          </div>
          <Field label="Serviço" required>
            <select
              value={form.serviceId || ""}
              onChange={(e) => update("serviceId", e.target.value)}
            >
              <option value="">Selecione o serviço</option>
              {services
                .filter((item: Service) => item.active)
                .map((item: Service) => (
                  <option value={item.id} key={item.id}>
                    {item.name} · {money(item.price)}
                  </option>
                ))}
            </select>
          </Field>
          <div className="form-grid">
            <Field label="Data" required>
              <input
                type="date"
                value={form.date || today}
                onChange={(e) => update("date", e.target.value)}
              />
            </Field>
            <Field label="Horário" required>
              <input
                type="time"
                value={form.time || ""}
                onChange={(e) => update("time", e.target.value)}
              />
            </Field>
          </div>
          <Field label="Profissional">
            <select
              value={form.professional || "Eliane"}
              onChange={(e) => update("professional", e.target.value)}
            >
              <option>Eliane</option>
              <option>Carla</option>
            </select>
          </Field>
        </>
      )}
      {type === "client" && (
        <>
          <div className="form-section-title">Dados do tutor</div>
          <Field label="Nome completo" required>
            <input
              value={form.name || ""}
              onChange={(e) => update("name", e.target.value)}
              placeholder="Ex.: Fernanda Souza"
            />
          </Field>
          <div className="form-grid">
            <Field label="Telefone" required>
              <input
                value={form.phone || ""}
                onChange={(e) => update("phone", e.target.value)}
                placeholder="(00) 00000-0000"
              />
            </Field>
            <Field label="E-mail">
              <input
                type="email"
                value={form.email || ""}
                onChange={(e) => update("email", e.target.value)}
                placeholder="email@exemplo.com"
              />
            </Field>
          </div>
          <div className="form-section-title">Dados do pet</div>
          <div className="form-grid">
            <Field label="Nome do pet" required>
              <input
                value={form.petName || ""}
                onChange={(e) => update("petName", e.target.value)}
              />
            </Field>
            <Field label="Raça" required>
              <input
                value={form.breed || ""}
                onChange={(e) => update("breed", e.target.value)}
              />
            </Field>
          </div>
          <Field label="Sexo">
            <select
              value={form.sex || ""}
              onChange={(e) => update("sex", e.target.value)}
            >
              <option value="">Selecione</option>
              <option>Macho</option>
              <option>Fêmea</option>
            </select>
          </Field>
        </>
      )}
      {type === "product" && (
        <>
          <Field label="Nome do produto" required>
            <input
              value={form.name || ""}
              onChange={(e) => update("name", e.target.value)}
            />
          </Field>
          <Field label="Categoria" required>
            <select
              value={form.category || ""}
              onChange={(e) => update("category", e.target.value)}
            >
              <option value="">Selecione</option>
              <option>Higiene</option>
              <option>Acessórios</option>
              <option>Brinquedos</option>
              <option>Alimentação</option>
            </select>
          </Field>
          <div className="form-grid form-grid--three">
            <Field label="Preço" required>
              <input
                type="number"
                step="0.01"
                value={form.price || ""}
                onChange={(e) => update("price", e.target.value)}
              />
            </Field>
            <Field label="Custo" required>
              <input
                type="number"
                step="0.01"
                value={form.cost || ""}
                onChange={(e) => update("cost", e.target.value)}
              />
            </Field>
            <Field label="Estoque" required>
              <input
                type="number"
                value={form.stock || ""}
                onChange={(e) => update("stock", e.target.value)}
              />
            </Field>
          </div>
        </>
      )}
      {type === "service" && (
        <>
          <Field label="Nome do serviço" required>
            <input
              value={form.name || ""}
              onChange={(e) => update("name", e.target.value)}
            />
          </Field>
          <div className="form-grid form-grid--three">
            <Field label="Preço" required>
              <input
                type="number"
                step="0.01"
                value={form.price || ""}
                onChange={(e) => update("price", e.target.value)}
              />
            </Field>
            <Field label="Custo" required>
              <input
                type="number"
                step="0.01"
                value={form.cost || ""}
                onChange={(e) => update("cost", e.target.value)}
              />
            </Field>
            <Field label="Duração (min)" required>
              <input
                type="number"
                value={form.duration || ""}
                onChange={(e) => update("duration", e.target.value)}
              />
            </Field>
          </div>
        </>
      )}
      {type === "sale" && (
        <>
          <Field label="Produto" required>
            <select
              value={form.productId || ""}
              onChange={(e) => update("productId", e.target.value)}
            >
              <option value="">Selecione o produto</option>
              {products.map((item: Product) => (
                <option value={item.id} key={item.id}>
                  {item.name} · {item.stock} em estoque
                </option>
              ))}
            </select>
          </Field>
          <div className="form-grid">
            <Field label="Quantidade" required>
              <input
                type="number"
                min="1"
                value={form.quantity || "1"}
                onChange={(e) => update("quantity", e.target.value)}
              />
            </Field>
            <Field label="Cliente (opcional)">
              <input
                value={form.client || ""}
                onChange={(e) => update("client", e.target.value)}
                placeholder="Venda balcão"
              />
            </Field>
          </div>
          {form.productId && (
            <div className="sale-total">
              <span>Total da venda</span>
              <strong>
                {money(
                  (products.find(
                    (item: Product) => item.id === Number(form.productId),
                  )?.price || 0) * Number(form.quantity || 1),
                )}
              </strong>
            </div>
          )}
        </>
      )}
      {type === "transaction" && (
        <>
          <div className="form-grid">
            <Field label="Tipo" required>
              <select
                value={form.type || ""}
                onChange={(e) => update("type", e.target.value)}
              >
                <option value="">Selecione</option>
                <option>Receita</option>
                <option>Custo</option>
                <option>Comissão</option>
              </select>
            </Field>
            <Field label="Data">
              <input
                type="date"
                value={form.date || today}
                onChange={(e) => update("date", e.target.value)}
              />
            </Field>
          </div>
          <Field label="Descrição" required>
            <input
              value={form.description || ""}
              onChange={(e) => update("description", e.target.value)}
              placeholder="Ex.: Compra de insumos"
            />
          </Field>
          <Field label="Valor" required>
            <input
              type="number"
              step="0.01"
              value={form.value || ""}
              onChange={(e) => update("value", e.target.value)}
            />
          </Field>
        </>
      )}
      {error && (
        <div className="form-error">
          <AlertTriangle size={16} />
          {error}
        </div>
      )}
      <div className="modal-actions">
        <Button variant="secondary" onClick={close}>
          Cancelar
        </Button>
        <Button type="submit">
          <Check size={16} /> Confirmar
        </Button>
      </div>
    </form>
  )
}
