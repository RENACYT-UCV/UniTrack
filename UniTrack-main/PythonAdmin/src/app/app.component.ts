import { Component } from '@angular/core';
import { MenuController, NavController } from '@ionic/angular';
import { Router, NavigationEnd } from '@angular/router';

@Component({
  selector: 'app-root',
  templateUrl: 'app.component.html',
  styleUrls: ['app.component.scss'],
})
export class AppComponent {
  constructor(
    private menuCtrl: MenuController,
    private router: Router,
    private navCtrl: NavController
  ) {
    this.setupMenu();
  }

  private setupMenu() {
    // Habilitar menú al iniciar
    this.menuCtrl.enable(true, 'main-menu');

    // Cerrar menú al navegar
    this.router.events.subscribe(event => {
      if (event instanceof NavigationEnd) {
        this.menuCtrl.close('main-menu');
      }
    });
  }

  // Método para cerrar sesión (llamado desde el ítem del menú)
  public async logout() {
    await this.menuCtrl.enable(false, 'main-menu'); // Deshabilita el menú
    this.navCtrl.navigateRoot('/login'); // Redirige a login
    this.menuCtrl.close('main-menu'); // Cierra el menú visualmente
  }
}