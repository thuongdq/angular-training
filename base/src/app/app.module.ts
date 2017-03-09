import './rxjs-operators';
import { NgModule }      from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { FormsModule }   from '@angular/forms';
import { HttpModule }    from '@angular/http';
import { Routes, RouterModule } from '@angular/router';

import { AppComponent }  from './app.component';
import { Config } from './app.config';

//Component
import { CategoryListComponent } from './components/categories/category.component'

//services
import { CategoryService } from './components/categories/category.service'

const appRoutes: Routes = [
  { path: 'categories', component: CategoryListComponent }
];


@NgModule({
  declarations: [
    AppComponent,
    CategoryListComponent
  ],
  imports: [
    BrowserModule,
    FormsModule,
    HttpModule,
    RouterModule.forRoot(appRoutes)
  ],
  providers: [
    Config,
    CategoryService
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }
