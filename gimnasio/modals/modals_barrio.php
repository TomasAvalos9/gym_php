<!-- MODAL NUEVO BARRIO -->
<div class="modal fade" id="modalBarrio" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Nuevo Barrio</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST">
            <input type="hidden" name="form_type" value="barrio">
            <div class="mb-3">
                <label>Nombre</label>
                <input type="text" name="barrio" class="form-control" required>
            </div>
            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
        <?php if(isset($error_barrio)): ?>
            <div class="alert alert-danger mt-2"><?= $error_barrio; ?></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- MODALES EDITAR BARRIO -->
<?php foreach($barrios as $bar): ?>
<div class="modal fade" id="modalBarrio<?= $bar['idbarrio']; ?>" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Editar Barrio</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST">
            <input type="hidden" name="form_type" value="barrio">
            <input type="hidden" name="idbarrio" value="<?= $bar['idbarrio']; ?>">
            <div class="mb-3">
                <label>Nombre</label>
                <input type="text" name="barrio" class="form-control" value="<?= htmlspecialchars($bar['barrio']); ?>" required>
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
