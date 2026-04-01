import { Routes } from '@angular/router';
import { MainComponent } from './pages/main/main.component';
import { ProfileComponent } from './pages/profile/profile.component';
import { DataEnterComponent } from './pages/data-enter/data-enter.component';
import { AccessdeniedComponent } from './pages/accessdenied/accessdenied.component';
import { LoginComponent } from './pages/login/login.component';
import { terminalGuard } from './guards/terminal.guard';
import { LoadingComponent } from './pages/loading/loading.component';
import { StatsComponent } from './pages/stats/stats.component';
import { SettingsComponent } from './pages/settings/settings.component';
import { BuyPageComponent } from './pages/buy-page/buy-page.component';


export const routes: Routes = [
  {path: '', component: LoadingComponent, pathMatch: 'full'},
  {path: 'main', component: MainComponent, pathMatch: 'full', canActivate: [terminalGuard],},
  {path: 'profile', component: ProfileComponent},
  {path: 'dataenter', component: DataEnterComponent},
  {path: 'accessdenied', component: AccessdeniedComponent},
  {path: 'login', component: LoginComponent},
  {path: 'stats', component: StatsComponent},
  {path: 'settings', component: SettingsComponent},
  {path: 'buy', component: BuyPageComponent},
];

