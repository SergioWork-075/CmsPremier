<h3>
    <a href="<?php echo $_SESSION['home'] ?>" title="Inicio">Inicio</a> <span>| </span>
    <a href="<?php echo $_SESSION['home'] ?>equipos" title="equipos">equipos</a> <span>| </span>
    <span><?php echo $datos->nombre ?></span>
</h3>
<div class="row">
    <article class="col s12">
        <div class="card horizontal large equipo">
            <div class="card-image">
                <img src="<?php echo $_SESSION['public']."img/".$datos->imagen ?>" alt="<?php echo $datos->nombre ?>">
            </div>
            <div class="card-stacked">
                <div class="card-content">
                    <h4><?php echo $datos->nombre ?></h4>
                    <p><?php echo $datos->introduccion ?></p>
                    <p><?php echo $datos->descripcion ?></p>
                    <br>
                    <p>
                        <strong>Fecha</strong>: <?php echo date("d/m/Y", strtotime($datos->fecha)) ?><br>
                        <strong>Provincia</strong>: <?php echo $datos->provincia ?>
                    </p>
                </div>
            </div>
        </div>
    </article>
</div>
