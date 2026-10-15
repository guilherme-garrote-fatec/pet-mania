import { Search } from "lucide-react"

export default function EmptyState({ text }: { text: string }) {
  return (
    <div className="empty-state">
      <Search size={28} />
      <strong>Nenhum resultado</strong>
      <span>{text}</span>
    </div>
  )
}
