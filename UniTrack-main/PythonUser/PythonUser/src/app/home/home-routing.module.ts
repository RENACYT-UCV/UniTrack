import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { HomePage } from './home.page';  // Asegúrate de que el componente HomePage esté importado correctamente

const routes: Routes = [
  {
    path: '',
    component: HomePage  // Esto asegura que esta página se muestre cuando navegas a '/home'
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class HomePageRoutingModule {}
