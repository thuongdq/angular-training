import { Injectable } from '@angular/core';

@Injectable()

export class Config {
    private configArr = {
        "baseUrl" : "http://localhost/training/",
        "apiUrl": "http://localhost/angular-training/base/api/",
    };

    get(key:string){
        if(typeof key === 'string' && this.configArr[key]){
            return this.configArr[key];
        }
        return this.configArr[key];
    }

    add(key:string, value:any){
        if(typeof key === 'string' && !this.configArr[key]){
             this.configArr[key] = value;
             return true;
        }
        return false;
    }

    update(key:string, value:any){
        if(typeof key === 'string'){
             this.configArr[key] = value;
             return true;
        }
        return false;
    }

    delete(key:string){
        if(typeof key === 'string' && this.configArr[key]){
            delete this.configArr[key];
            return true;
        }
        return false;
    }
}