/**
 * Utilidad de resolución de imagen para productos de ferretería.
 *
 * REGLAS ESTRICTAS:
 * 1. Prioriza la imagen propia subida por el usuario o asignada directamente (campo `imagen`/`imagen_url` en BD).
 * 2. Si no tiene foto propia, retorna `null` para que la interfaz muestre el contenedor técnico
 *    profesional con el icono de ferretería acorde a su categoría.
 * 3. PROHIBIDO retornar URLs externas no controladas (Unsplash genéricas) que causan fotos desalineadas
 *    (como relojes o paisajes en cables y tomacorrientes).
 */

/**
 * Obtiene la URL de la imagen si el producto tiene una asignada.
 *
 * @param {object|null} product - Objeto del producto
 * @returns {string|null} URL de la imagen o null si no tiene foto propia
 */
export function getProductImage(product) {
    if (!product) {
        return null;
    }

    // 1. Atributo accessor imagen_url del modelo Producto
    if (product.imagen_url && typeof product.imagen_url === 'string' && product.imagen_url.trim() !== '') {
        return product.imagen_url.trim();
    }

    // 2. Campo directo imagen en BD
    if (product.imagen && typeof product.imagen === 'string' && product.imagen.trim() !== '') {
        const trimmed = product.imagen.trim();
        if (trimmed.startsWith('http://') || trimmed.startsWith('https://')) {
            return trimmed;
        }
        return `/storage/${trimmed.replace(/^\/+/, '')}`;
    }

    return null;
}
