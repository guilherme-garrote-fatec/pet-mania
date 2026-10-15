import type { ReactNode } from "react"

export default function Field({
  label,
  required,
  children,
}: {
  label: string
  required?: boolean
  children: ReactNode
}) {
  return (
    <label className="field">
      <span>
        {label}
        {required && <b>*</b>}
      </span>
      {children}
    </label>
  )
}
