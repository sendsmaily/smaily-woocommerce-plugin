
<h3>Newsletter</h3>

<div class="smailey_newsletter">
<?php if(array_key_exists('success',$_POST) || array_key_exists('error',$_POST)): ?>
<div class="alert <?= (array_key_exists('success',$_POST))?'success':'danger'; ?>">
  <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span> 
	<?= (array_key_exists('success',$_POST))?$_POST['success']:$_POST['error']; ?>
</div>
<?php endif; ?>
  <form action="" method="POST">
    <label for="fname">Your Name</label>
    <input type="text" id="fname" name="firstname" placeholder="Your name.." required />

    <label for="lname">Email</label>
    <input type="text" id="lname" name="email" placeholder="Your last name.." required />
 
    <input type="submit" value="Submit">
  </form>
</div>