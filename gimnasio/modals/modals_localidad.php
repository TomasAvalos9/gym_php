<!-- MODAL NUEVA LOCALIDAD -->
<div class="modal fade" id="modalLocalidad" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Nueva Localidad</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST">
            <input type="hidden" name="form_type" value="localidad">
            <div class="mb-3">
                <label>Nombre</label>
                <input type="text" name="localidad" class="form-control" required>
            </div>
            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
        <?php if(isset($error_localidad)): ?>
            <div class="alert alert-danger mt-2"><?= $error_localidad; ?></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- MODALES EDITAR LOCALIDAD -->
<?php foreach($localidades as $loc): ?>
<div class="modal fade" id="modalLocalidad<?= $loc['idlocalidad']; ?>" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Editar Localidad</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST">
            <input type="hidden" name="form_type" value="localidad">
            <input type="hidden" name="idlocalidad" value="<?= $loc['idlocalidad']; ?>">
            <div class="mb-3">
                <label>Nombre</label>
                <input type="text" name="localidad" class="form-control" value="<?= htmlspecialchars($loc['localidad']); ?>" required>
            </div>
            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>
