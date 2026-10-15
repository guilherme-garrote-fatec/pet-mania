import type { ReactNode } from "react"

export default function Button({
  children,
  variant = "primary",
  onClick,
  type = "button",
  disabled,
  className = "",
}: {
  children: ReactNode
  variant?: "primary" | "secondary" | "ghost" | "danger"
  onClick?: () => void
  type?: "button" | "submit"
  disabled?: boolean
  className?: string
}) {
  return (
    <button
      className={`button button--${variant} ${className}`}
      onClick={onClick}
      type={type}
      disabled={disabled}
    >
      {children}
    </button>
  )
}
