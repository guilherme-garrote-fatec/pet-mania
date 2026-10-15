import type {
  Appointment,
  Client,
  Product,
  Sale,
  Service,
  Transaction,
} from "../types/domain"

export const today = "2026-05-18"

export const initialClients: Client[] = [
  {
    id: 1,
    name: "Mariana Costa",
    phone: "(18) 99124-8033",
    email: "mariana@email.com",
    pets: [
      { id: 11, name: "Bento", breed: "Golden Retriever", sex: "Macho" },
      { id: 12, name: "Lola", breed: "Shih-tzu", sex: "Fêmea" },
    ],
  },
  {
    id: 2,
    name: "Rafael Nunes",
    phone: "(18) 99732-1045",
    email: "rafael@email.com",
    pets: [{ id: 21, name: "Theo", breed: "Spitz Alemão", sex: "Macho" }],
  },
  {
    id: 3,
    name: "Beatriz Lima",
    phone: "(18) 98813-4420",
    email: "bia.lima@email.com",
    pets: [{ id: 31, name: "Amora", breed: "Poodle", sex: "Fêmea" }],
  },
  {
    id: 4,
    name: "Lucas Almeida",
    phone: "(18) 99641-7820",
    email: "lucas@email.com",
    pets: [{ id: 41, name: "Max", breed: "Bulldog Francês", sex: "Macho" }],
  },
]

export const initialServices: Service[] = [
  {
    id: 1,
    name: "Banho completo",
    price: 75,
    cost: 19,
    duration: 60,
    active: true,
  },
  {
    id: 2,
    name: "Banho + tosa",
    price: 125,
    cost: 31,
    duration: 90,
    active: true,
  },
  {
    id: 3,
    name: "Tosa higiênica",
    price: 55,
    cost: 10,
    duration: 45,
    active: true,
  },
  {
    id: 4,
    name: "Hidratação premium",
    price: 48,
    cost: 16,
    duration: 30,
    active: true,
  },
]

export const initialProducts: Product[] = [
  {
    id: 1,
    name: "Shampoo neutro 500ml",
    category: "Higiene",
    stock: 14,
    price: 39.9,
    cost: 20,
  },
  {
    id: 2,
    name: "Perfume Pet Fresh",
    category: "Higiene",
    stock: 5,
    price: 34.9,
    cost: 16,
  },
  {
    id: 3,
    name: "Coleira Comfort",
    category: "Acessórios",
    stock: 22,
    price: 49.9,
    cost: 23,
  },
  {
    id: 4,
    name: "Condicionador Soft",
    category: "Higiene",
    stock: 3,
    price: 42,
    cost: 21,
  },
  {
    id: 5,
    name: "Brinquedo mordedor",
    category: "Brinquedos",
    stock: 18,
    price: 27.5,
    cost: 11,
  },
]

export const initialAppointments: Appointment[] = [
  {
    id: 1,
    date: today,
    time: "08:30",
    clientId: 1,
    petId: 11,
    serviceId: 1,
    professional: "Eliane",
    status: "Concluído",
  },
  {
    id: 2,
    date: today,
    time: "10:00",
    clientId: 2,
    petId: 21,
    serviceId: 2,
    professional: "Eliane",
    status: "Em andamento",
  },
  {
    id: 3,
    date: today,
    time: "13:30",
    clientId: 3,
    petId: 31,
    serviceId: 3,
    professional: "Carla",
    status: "Agendado",
  },
  {
    id: 4,
    date: today,
    time: "15:00",
    clientId: 1,
    petId: 12,
    serviceId: 4,
    professional: "Carla",
    status: "Agendado",
  },
  {
    id: 5,
    date: "2026-05-19",
    time: "09:00",
    clientId: 4,
    petId: 41,
    serviceId: 1,
    professional: "Eliane",
    status: "Agendado",
  },
]

export const initialTransactions: Transaction[] = [
  {
    id: 1,
    date: "2026-05-18",
    description: "Banho completo · Bento",
    type: "Receita",
    value: 75,
  },
  {
    id: 2,
    date: "2026-05-18",
    description: "Insumos · Banho Bento",
    type: "Custo",
    value: 19,
  },
  {
    id: 3,
    date: "2026-05-18",
    description: "Comissão · Eliane",
    type: "Comissão",
    value: 11.25,
  },
  {
    id: 4,
    date: "2026-05-17",
    description: "Vendas no balcão",
    type: "Receita",
    value: 386.4,
  },
  {
    id: 5,
    date: "2026-05-17",
    description: "Reposição de produtos",
    type: "Custo",
    value: 128,
  },
  {
    id: 6,
    date: "2026-05-16",
    description: "Serviços realizados",
    type: "Receita",
    value: 490,
  },
  {
    id: 7,
    date: "2026-05-16",
    description: "Comissões da equipe",
    type: "Comissão",
    value: 73.5,
  },
  {
    id: 8,
    date: "2026-05-15",
    description: "Serviços realizados",
    type: "Receita",
    value: 635,
  },
  {
    id: 9,
    date: "2026-05-15",
    description: "Insumos de estética",
    type: "Custo",
    value: 142,
  },
]

export const initialSales: Sale[] = [
  {
    id: 1,
    date: "2026-05-18",
    productId: 3,
    quantity: 1,
    client: "Mariana Costa",
    total: 49.9,
  },
  {
    id: 2,
    date: "2026-05-17",
    productId: 1,
    quantity: 2,
    client: "Venda balcão",
    total: 79.8,
  },
  {
    id: 3,
    date: "2026-05-17",
    productId: 5,
    quantity: 1,
    client: "Rafael Nunes",
    total: 27.5,
  },
]

export const demoUser = {
  name: "Sônia Martins",
  email: "admin@petmania.com",
  password: "123456",
  role: "Administradora",
}
