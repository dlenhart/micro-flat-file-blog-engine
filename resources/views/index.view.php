<?php require('partials/header.view.php'); ?>

<?php if (count($page->items()) <= 0): ?>
  <div class="row">
        <div class="middlecolumn">
            <div class="card">
              No posts found. Check configuration or start writing!
            </div>
        </div>
  </div>
<?php else: ?>
  <?php foreach ($page->items() as $post): ?>
      <div class="row">
        <div class="middlecolumn">
            <div class="card">
              <h2><?= $post['meta']['title']; ?></h2>
              <h5>By <?= $post['meta']['author']; ?> &middot; <?= $post['meta']['date']; ?></h5>
              <p><?= $post['content']; ?>...</p>
              <a href="/post/<?= $post['file']; ?>">Read more &rarr;</a>
            </div>
        </div>
      </div>
  <?php endforeach; ?>

  <?php if ($page->hasPrevious() || $page->hasNext()): ?>
    <nav class="pagination">
      <?php if ($page->hasPrevious()): ?>
        <a href="/?page=<?= $page->previousPage(); ?>">&larr; Newer</a>
      <?php else: ?>
        <span></span>
      <?php endif; ?>

      <span class="pagination-status">Page <?= $page->currentPage(); ?> of <?= $page->totalPages(); ?></span>

      <?php if ($page->hasNext()): ?>
        <a href="/?page=<?= $page->nextPage(); ?>">Older &rarr;</a>
      <?php else: ?>
        <span></span>
      <?php endif; ?>
    </nav>
  <?php endif; ?>
<?php endif; ?>

<?php require('partials/footer.view.php'); ?>
