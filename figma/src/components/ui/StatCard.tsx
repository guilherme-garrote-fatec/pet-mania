import type { LucideIcon } from "lucide-react"
import { ArrowUpRight } from "lucide-react"

export default function StatCard({
  label,
  value,
  detail,
  icon: Icon,
  tone = "green",
}: {
  label: string
  value: string
  detail: string
  icon: LucideIcon
  tone?: "green" | "coral" | "blue" | "gold"
}) {
  return (
    <article className="stat-card">
      <div className={`stat-icon stat-icon--${tone}`}>
        <Icon size={20} />
      </div>
      <div className="stat-copy">
        <span>{label}</span>
        <strong>{value}</strong>
        <small>
          <ArrowUpRight size={13} /> {detail}
        </small>
      </div>
    </article>
  )
}
