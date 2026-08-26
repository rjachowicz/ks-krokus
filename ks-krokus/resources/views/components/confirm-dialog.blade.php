<dialog
    class="confirm-dialog"
    aria-labelledby="confirm-dialog-title"
    aria-describedby="confirm-dialog-message"
    data-confirm-dialog
>
    <form method="dialog">
        <h2 id="confirm-dialog-title">Potwierdź operację</h2>
        <p id="confirm-dialog-message" data-confirm-message></p>
        <div class="confirm-dialog__actions">
            <button type="submit" value="cancel" class="btn btn-secondary">Anuluj</button>
            <button type="button" class="btn btn-danger" data-confirm-accept>Potwierdź</button>
        </div>
    </form>
</dialog>
