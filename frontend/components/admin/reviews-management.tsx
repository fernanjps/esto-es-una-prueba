"use client"

import { useState, useEffect } from "react"
import { Button } from "@/components/ui/button"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Star, Trash2, Eye } from "lucide-react"
import Link from "next/link"

interface Review {
  id: number
  rating: number
  comment: string
  created_at: string
  user: {
    id: number
    name: string
  }
  game: {
    id: number
    title: string
  }
}

interface ReviewsManagementProps {
  token: string | null
}

export default function ReviewsManagement({ token }: ReviewsManagementProps) {
  const [reviews, setReviews] = useState<Review[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [success, setSuccess] = useState<string | null>(null)

  useEffect(() => {
    fetchReviews()
  }, [])

  const fetchReviews = async () => {
    try {
      const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/admin/reviews`, {
        headers: {
          Authorization: `Bearer ${token}`,
          "Content-Type": "application/json",
        },
      })

      if (response.ok) {
        const data = await response.json()
        setReviews(data.data || [])
      }
    } catch (error) {
      setError("Error al cargar las reseñas")
    } finally {
      setLoading(false)
    }
  }

  const handleDeleteReview = async (reviewId: number) => {
    if (!confirm("¿Estás seguro de que quieres eliminar esta reseña? Esta acción no se puede deshacer.")) {
      return
    }

    try {
      const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/admin/reviews/${reviewId}`, {
        method: "DELETE",
        headers: {
          Authorization: `Bearer ${token}`,
          "Content-Type": "application/json",
        },
      })

      if (response.ok) {
        setSuccess("Reseña eliminada exitosamente")
        fetchReviews()
      } else {
        setError("Error al eliminar la reseña")
      }
    } catch (error) {
      setError("Error de conexión")
    }
  }

  if (loading) {
    return <div className="text-white">Cargando reseñas...</div>
  }

  return (
    <div>
      {error && (
        <div className="mb-4 bg-red-900/50 border border-red-700 rounded-lg p-3">
          <div className="text-red-200 text-sm">{error}</div>
        </div>
      )}
      {success && (
        <div className="mb-4 bg-green-900/50 border border-green-700 rounded-lg p-3">
          <div className="text-green-200 text-sm">{success}</div>
        </div>
      )}

      <div className="overflow-x-auto">
        <Table>
          <TableHeader>
            <TableRow className="border-slate-700">
              <TableHead className="text-slate-300">Usuario</TableHead>
              <TableHead className="text-slate-300">Juego</TableHead>
              <TableHead className="text-slate-300">Rating</TableHead>
              <TableHead className="text-slate-300">Comentario</TableHead>
              <TableHead className="text-slate-300">Fecha</TableHead>
              <TableHead className="text-slate-300">Acciones</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {reviews.map((review) => (
              <TableRow key={review.id} className="border-slate-700">
                <TableCell>
                  <div className="text-white font-medium">{review.user.name}</div>
                </TableCell>
                <TableCell>
                  <div className="text-white">{review.game.title}</div>
                </TableCell>
                <TableCell>
                  <div className="flex items-center gap-1">
                    {[...Array(5)].map((_, i) => (
                      <Star
                        key={i}
                        className={`w-4 h-4 ${i < review.rating ? "fill-yellow-400 text-yellow-400" : "text-slate-600"}`}
                      />
                    ))}
                    <span className="text-white ml-1">{review.rating}</span>
                  </div>
                </TableCell>
                <TableCell>
                  <div className="text-slate-300 max-w-xs truncate">{review.comment}</div>
                </TableCell>
                <TableCell>
                  <div className="text-slate-400 text-sm">{new Date(review.created_at).toLocaleDateString()}</div>
                </TableCell>
                <TableCell>
                  <div className="flex gap-2">
                    <Link href={`/games/${review.game.id}`}>
                      <Button
                        size="sm"
                        variant="outline"
                        className="border-slate-600 text-white hover:bg-slate-700 bg-transparent"
                      >
                        <Eye className="w-3 h-3" />
                      </Button>
                    </Link>
                    <Button
                      size="sm"
                      variant="outline"
                      onClick={() => handleDeleteReview(review.id)}
                      className="border-red-600 text-red-400 hover:bg-red-900/20"
                    >
                      <Trash2 className="w-3 h-3" />
                    </Button>
                  </div>
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </div>

      {reviews.length === 0 && (
        <div className="text-center py-8">
          <div className="text-slate-400">No hay reseñas registradas</div>
        </div>
      )}
    </div>
  )
}
