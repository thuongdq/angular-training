import { Component } from '@angular/core';
import { Config } from './app.config';
import { CategoryService } from './components/categories/category.service'
@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent {
  title = 'app works!';
  private baseUrl:string;
  constructor(
    private _config: Config,
    private _Category: CategoryService
  ){}

  ngOnInit(){
    this._config.update('baseUrl', 'http://localhost:4200/');
    this.baseUrl = this._config.get('baseUrl');
    this._Category.list({
      success: (results : any) => {debugger;}
    });
  }
}
