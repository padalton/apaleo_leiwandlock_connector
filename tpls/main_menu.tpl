<nav>
    <ul>
        <li
        <?php if($menu_current_view == 'home') echo 'aria-current="page"'; ?>><a href="/">Home</a></li>
        <li
        <?php if($menu_current_view == 'setup_properties') echo 'aria-current="page"'; ?>><a href="?property_menue">Property
            Setup</a></li>
        <li
        <?php if($menu_current_view == 'setup_units') echo 'aria-current="page"'; ?>><a href="?units_menue">Units
            Setup</a></li>
        <li
        <?php if($menu_current_view == 'setup_emails') echo 'aria-current="page"'; ?>><a href="?emails_menue">Email
            Setup</a></li>
        <li><a href="?logout">Logout</a></li>
    </ul>
</nav>