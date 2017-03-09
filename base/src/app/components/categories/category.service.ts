import { Injectable } from '@angular/core';
import { Config } from '../../app.config';
import { Headers, Http } from '@angular/http';

@Injectable()
export class CategoryService {
    constructor(
        private _http: Http,
        private _config: Config
    ) { }

    list(callback: any, page: number = 0) {
        this._http.get(this._config.get("apiUrl") + "categories?page=" + page)
            .map(results => results.json())
            .subscribe(callback.success, callback.error);
    }

    create(data: any, callback: any) {
        if (typeof data != 'undefined') {
            this._http.post(this._config.get("apiUrl") + "categories/create", data)
                .map(results => results.json())
                .subscribe(callback.success, callback.error);
        }
    }

    show(id: number = 0, callback: any) {
        this._http.get(this._config.get("apiUrl") + "categories/" + id + "/show")
            .map(results => results.json())
            .subscribe(callback.success, callback.error);
    }

    update(id: number = 0, data: any, callback: any) {
        if (typeof data != 'undefined') {
            this._http.put(this._config.get("apiUrl") + "categories/" + id + "/update", data)
                .map(results => results.json())
                .subscribe(callback.success, callback.error);
        }
    }

    delete(id: number = 0, callback: any) {
        this._http.delete(this._config.get("apiUrl") + "categories/" + id + "/delete")
            .map(results => results.json())
            .subscribe(callback.success, callback.error);
    }
}