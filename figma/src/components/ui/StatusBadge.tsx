import type { AppointmentStatus } from "../../types/domain"

export default function StatusBadge({
  status,
}: {
  status: AppointmentStatus | "Ativo" | "Inativo" | "Estoque baixo"
}) {
  const cls = status.toLowerCase().replace(" ", "-").replace("í", "i")
  return <span className={`status status--${cls}`}>{status}</span>
}
