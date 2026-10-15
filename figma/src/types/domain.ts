export type Page = "overview" | "schedule" | "clients" | "products" | "services" | "sales" | "finance" | "reports"
export type AppointmentStatus = "Agendado" | "Em andamento" | "Concluído" | "Cancelado"
export type ModalType = "appointment" | "client" | "product" | "service" | "sale" | "transaction" | null

export type Pet = {
  id: number
  name: string
  breed: string
  sex: string
}
export type Client = {
  id: number
  name: string
  phone: string
  email: string
  pets: Pet[]
}
export type Service = {
  id: number
  name: string
  price: number
  cost: number
  duration: number
  active: boolean
}
export type Product = {
  id: number
  name: string
  category: string
  stock: number
  price: number
  cost: number
}
export type Appointment = {
  id: number
  date: string
  time: string
  clientId: number
  petId: number
  serviceId: number
  professional: string
  status: AppointmentStatus
}
export type Transaction = {
  id: number
  date: string
  description: string
  type: "Receita" | "Custo" | "Comissão"
  value: number
}
export type Sale = {
  id: number
  date: string
  productId: number
  quantity: number
  client: string
  total: number
}
export type AppUser = {
  name: string
  email: string
  password: string
  role: string
}
export type FinancialTotals = {
  revenue: number
  costs: number
  commissions: number
  profit: number
}
export type FormValues = Record<string, string>
export type OpenModal = (type: ModalType, defaults?: FormValues) => void
