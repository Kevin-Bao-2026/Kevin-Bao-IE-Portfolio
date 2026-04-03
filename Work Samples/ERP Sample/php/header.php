<?php
// global/header.php
?>
<style>
  .header-info {
    position: absolute;
    top: 16px;
    right: 24px;
    font-weight: bold;
    color: #333;
    z-index: 100;
  }
</style>

<div class="header-info" id="header-info"></div>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const name = sessionStorage.getItem('name');
    const id   = sessionStorage.getItem('id');
    if (name && id) {
      document.getElementById('header-info').textContent =
        name + ' (ID: ' + id + ')';
    }
  });
</script>
