// Actualiza la previsualización de imagen:
<img src="<?php echo !empty($registro['foto']) ? '../img/' . $registro['foto'] : '../img/no-foto.jpg'; ?>" alt="Foto del registro">