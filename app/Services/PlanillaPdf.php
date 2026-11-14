<?php

namespace App\Services;

/**
 * Genera un PDF de una tabla en A4 apaisado, sin librerías externas.
 * Usa Helvetica (fuente base de PDF) con codificación Windows-1252 para tildes y ñ.
 */
class PlanillaPdf
{
    private const ANCHO = 842;

    private const ALTO = 595;

    private const MARGEN = 36;

    private const ALTURA_FILA = 16;

    private const TAMANIO_TEXTO = 9;

    private const TAMANIO_TITULO = 14;

    /**
     * @param  list<string>  $encabezado  Líneas sobre la tabla; la primera va como título
     * @param  list<array{titulo: string, ancho: int}>  $columnas  Anchos en puntos (máximo 770)
     * @param  list<list<string>>  $filas
     */
    public function generar(array $encabezado, array $columnas, array $filas): string
    {
        return $this->armarDocumento($this->paginar($encabezado, $columnas, $filas));
    }

    /** @return list<string> Contenido de cada página */
    private function paginar(array $encabezado, array $columnas, array $filas): array
    {
        $paginas = [];
        $contenido = '';
        $y = self::ALTO - self::MARGEN;

        foreach ($encabezado as $indice => $linea) {
            $titulo = $indice === 0;
            $contenido .= $this->texto($linea, self::MARGEN, $y, $titulo ? self::TAMANIO_TITULO : 10, $titulo);
            $y -= $titulo ? 22 : 14;
        }

        $y -= 8;
        $contenido .= $this->cabeceraTabla($columnas, $y);
        $y -= self::ALTURA_FILA;

        if ($filas === []) {
            $contenido .= $this->texto('No hay feriantes inscriptos en este día.', self::MARGEN, $y, 10, false);
        }

        foreach ($filas as $fila) {
            if ($y < self::MARGEN + self::ALTURA_FILA) {
                $paginas[] = $contenido;
                $contenido = '';
                $y = self::ALTO - self::MARGEN;
                $contenido .= $this->cabeceraTabla($columnas, $y);
                $y -= self::ALTURA_FILA;
            }

            $contenido .= $this->filaTabla($columnas, $fila, $y);
            $y -= self::ALTURA_FILA;
        }

        $paginas[] = $contenido;

        return $paginas;
    }

    private function cabeceraTabla(array $columnas, float $y): string
    {
        $salida = sprintf(
            "0.87 g %d %.2f %d %d re f\n0 g\n",
            self::MARGEN,
            $y - 4,
            $this->anchoTotal($columnas),
            self::ALTURA_FILA,
        );

        $x = self::MARGEN;
        foreach ($columnas as $columna) {
            $salida .= $this->texto($columna['titulo'], $x + 3, $y, self::TAMANIO_TEXTO, true, $columna['ancho'] - 6);
            $x += $columna['ancho'];
        }

        return $salida;
    }

    private function filaTabla(array $columnas, array $fila, float $y): string
    {
        $salida = '';
        $x = self::MARGEN;
        foreach ($columnas as $indice => $columna) {
            $salida .= $this->texto((string) ($fila[$indice] ?? ''), $x + 3, $y, self::TAMANIO_TEXTO, false, $columna['ancho'] - 6);
            $x += $columna['ancho'];
        }

        // Línea separadora debajo de la fila
        $salida .= sprintf(
            "0.75 G 0.5 w %d %.2f m %d %.2f l S\n",
            self::MARGEN,
            $y - 4,
            self::MARGEN + $this->anchoTotal($columnas),
            $y - 4,
        );

        return $salida;
    }

    private function texto(string $valor, float $x, float $y, int $tamanio, bool $negrita, ?int $anchoMaximo = null): string
    {
        if ($anchoMaximo !== null) {
            // Aproximación: un carácter de Helvetica ocupa unos 0,55 veces el tamaño de la fuente
            $cantidad = max(1, (int) floor($anchoMaximo / ($tamanio * 0.55)));
            $valor = mb_strimwidth($valor, 0, $cantidad, '…');
        }

        return sprintf(
            "BT /%s %d Tf %.2f %.2f Td %s Tj ET\n",
            $negrita ? 'F2' : 'F1',
            $tamanio,
            $x,
            $y,
            $this->cadena($valor),
        );
    }

    /** Cadena PDF entre paréntesis, en Windows-1252 y con los caracteres especiales escapados. */
    private function cadena(string $valor): string
    {
        $valor = str_replace(["\r", "\n"], ' ', $valor);
        $latin = mb_convert_encoding($valor, 'Windows-1252', 'UTF-8');

        return '('.str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $latin).')';
    }

    private function anchoTotal(array $columnas): int
    {
        return array_sum(array_column($columnas, 'ancho'));
    }

    /** Arma el archivo: catálogo, páginas, dos fuentes y, por página, contenido y objeto de página. */
    private function armarDocumento(array $paginas): string
    {
        $objetos = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
            4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>',
        ];

        $hijos = [];
        foreach ($paginas as $indice => $contenido) {
            $numeroContenido = 5 + 2 * $indice;
            $numeroPagina = $numeroContenido + 1;
            $hijos[] = "{$numeroPagina} 0 R";

            $objetos[$numeroContenido] = sprintf("<< /Length %d >>\nstream\n%s\nendstream", strlen($contenido), $contenido);
            $objetos[$numeroPagina] = sprintf(
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %d %d] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents %d 0 R >>',
                self::ANCHO,
                self::ALTO,
                $numeroContenido,
            );
        }

        $objetos[2] = sprintf('<< /Type /Pages /Kids [%s] /Count %d >>', implode(' ', $hijos), count($paginas));
        ksort($objetos);

        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $posiciones = [];
        foreach ($objetos as $numero => $cuerpo) {
            $posiciones[$numero] = strlen($pdf);
            $pdf .= "{$numero} 0 obj\n{$cuerpo}\nendobj\n";
        }

        $referencias = strlen($pdf);
        $total = count($objetos) + 1;
        $pdf .= "xref\n0 {$total}\n0000000000 65535 f \n";
        for ($numero = 1; $numero < $total; $numero++) {
            $pdf .= sprintf("%010d 00000 n \n", $posiciones[$numero]);
        }

        $pdf .= "trailer\n<< /Size {$total} /Root 1 0 R >>\nstartxref\n{$referencias}\n%%EOF";

        return $pdf;
    }
}
