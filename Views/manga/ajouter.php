<section class="section-content">

<h1>Ajouter un manga</h1>

<form action="/lolissr/manga/ajouterTraitement" method="POST" enctype="multipart/form-data">

    <div>
        <label for="livre">Livre</label>
        <input type="text" name="livre" id="livre" required>
    </div>

    <div>
        <label for="slug">Slug</label>
        <input type="text" name="slug" id="slug" required>
    </div>

    <div>
        <label for="numero">Numéro</label>
        <input type="number" name="numero" id="numero" min="1" required>
    </div>

    <div>
        <label for="image">Image</label>
        <input type="file" name="image" id="image" accept=".jpg,.jpeg,.png,.webp" required>
    </div>

    <button type="submit">Valider</button>
</form>

<?php if (!empty($_SESSION['success'])): ?>
    <p style="color: green; font-weight: bold;">
        <?= $_SESSION['success']; ?>
    </p>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

</section>