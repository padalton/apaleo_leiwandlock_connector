</div>
</main>
</div>
<script type="text/javascript">
    /*
    const list = mdc.list.MDCList.attachTo(document.querySelector('.mdc-list'));
    list.wrapFocus = true;
    const topAppBarElement = document.querySelector('.mdc-top-app-bar');
    const topAppBar = new mdc.topAppBar.MDCTopAppBar(topAppBarElement);
     */

    import {MDCTopAppBar} from '@material/top-app-bar';
    import {MDCDrawer} from "@material/drawer";
    import {MDCList} from "@material/list";

    // Select DOM elements

    const topAppBarElement = document.querySelector('.mdc-top-app-bar');
    const listEl = document.querySelector('.mdc-drawer .mdc-list');
    const drawerElement = document.querySelector('.mdc-drawer');
    const mainContentEl = document.querySelector('.main-content');

    // Initialize either modal or permanent drawer

    const initModalDrawer = () => {
        drawerElement.classList.add("mdc-drawer--modal");
        const drawer = MDCDrawer.attachTo(drawerElement);
        drawer.open = false;

        const topAppBar = MDCTopAppBar.attachTo(topAppBarElement);
        topAppBar.setScrollTarget(mainContentEl);
        topAppBar.listen('MDCTopAppBar:nav', () => {
            drawer.open = !drawer.open;
        });

        listEl.addEventListener('click', (event) => {
            drawer.open = false;
        });

        return drawer;
    }

    const initPermanentDrawer = () => {
        drawerElement.classList.remove("mdc-drawer--modal");
        const list = new MDCList(listEl);
        list.wrapFocus = true;
        return list;
    }

    let drawer = window.matchMedia("(max-width: 900px)").matches ?
        initModalDrawer() : initPermanentDrawer();

    // Toggle between permanent drawer and modal drawer at breakpoint 900px

    const resizeHandler = () => {
        if (window.matchMedia("(max-width: 900px)").matches && drawer instanceof MDCList) {
            drawer.destroy();
            drawer = initModalDrawer();
        } else if (window.matchMedia("(min-width: 900px)").matches && drawer instanceof MDCDrawer) {
            drawer.destroy();
            drawer = initPermanentDrawer();
        }
    }
    window.addEventListener('resize', resizeHandler);
</script>
</body>
</html>