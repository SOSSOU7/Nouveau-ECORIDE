export default class Route {
    constructor(url, title, pathHtml, pathJS = "") {
      this.url = url;
      this.title = title;
      this.pathHtml = pathHtml;
      this.pathJS = pathJS;
    }

    getFullPath() {
        return this.pathJS ? `${this.pathHtml}, ${this.pathJS}` : this.pathHtml;
    }
}