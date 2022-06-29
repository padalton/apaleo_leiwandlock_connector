<aside class="mdc-drawer">
    <div class="mdc-drawer__header">
        <h3 class="mdc-drawer__title">LeiwandLock</h3>
        <h6 class="mdc-drawer__subtitle">Apaleo Connector</h6>
    </div>
    <div class="mdc-drawer__content">
        <nav class="mdc-list">
            <a class="mdc-list-item <?php if($menu_current_view == 'home') echo 'mdc-list-item--activated'; ?>" href="/" <?php if($menu_current_view == 'home') echo 'aria-current="page"'; ?>>
                <span class="mdc-list-item__ripple"></span>
                <i class="material-icons mdc-list-item__graphic" aria-hidden="true">home</i>
                <span class="mdc-list-item__text">Home</span>
            </a>
            <a class="mdc-list-item <?php if($menu_current_view == 'setup_properties') echo 'mdc-list-item--activated'; ?>" href="?property_menue" <?php if($menu_current_view == 'setup_properties') echo 'aria-current="page"'; ?>>
                <span class="mdc-list-item__ripple"></span>
                <i class="material-icons mdc-list-item__graphic" aria-hidden="true">home_work</i>
                <span class="mdc-list-item__text">Property Setup</span>
            </a>
            <a class="mdc-list-item <?php if($menu_current_view == 'setup_units') echo 'mdc-list-item--activated'; ?>" href="?units_menue" <?php if($menu_current_view == 'setup_units') echo 'aria-current="page"'; ?>>
                <span class="mdc-list-item__ripple"></span>
                <i class="material-icons mdc-list-item__graphic" aria-hidden="true">settings</i>
                <span class="mdc-list-item__text">Units Setup</span>
            </a>
            <a class="mdc-list-item <?php if($menu_current_view == 'setup_emails') echo 'mdc-list-item--activated'; ?>" href="?emails_menue" <?php if($menu_current_view == 'setup_emails') echo 'aria-current="page"'; ?>>
                <span class="mdc-list-item__ripple"></span>
                <i class="material-icons mdc-list-item__graphic" aria-hidden="true">mail</i>
                <span class="mdc-list-item__text">Email Setup</span>
            </a>
            <a class="mdc-list-item" href="?logout">
                <span class="mdc-list-item__ripple"></span>
                <i class="material-icons mdc-list-item__graphic" aria-hidden="true">logout</i>
                <span class="mdc-list-item__text">Logout</span>
            </a>
        </nav>
    </div>
</aside>
<div class="mdc-drawer-app-content">
    <div class="mdc-top-app-bar mdc-top-app-bar__row">
        <section class="mdc-top-app-bar__section mdc-top-app-bar__section--align-start">
            <span class="mdc-top-app-bar__title">
                <?php
switch($menu_current_view):
    default:
        echo "Home";
        break;
    case "setup_properties":
        echo "Property Setup";
        break;
    case "setup_units":
        echo "Units Setup";
        break;
    case "setup_emails":
        echo "Email Setup";
        break;
endswitch;
?>
            </span>
        </section>
    </div>
    </header>
    <main class="main-content" id="main-content">
        <div class="mdc-top-app-bar--fixed-adjust">
