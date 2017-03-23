> ##### Package-Information
> This Sourcecode belongs to the webexcess open source initiative of Neos Packages built at dotpulse.

> Please have a look at the [Information-Page](https://webexcess.github.io/open-source-initiative/).
* * *

# Dotpulse.Form Package for Neos CMS #

## Installation
```
composer require dotpulse/form
```

## Configuration
*(Die Dokumentation ist noch nicht ganz komplett)*

### Page
- **label** (string)
Identifier for `<f:translate id="{your.label}" />`

### Form-Tag
- **containerClassAttribute** (string) *[Default: '‘]*
Fügt dem Form-Tag eine Class hinzu
- **multipartForm** (boolean) *[Default: FALSE]*
Render das Attribut `enctype="multipart/form-data"`
- **renderSubmitButton** (boolean) *[Default: TRUE]*
Wenn auf `TRUE` wird am Ende des Formulars die Navigation (Submit-Button) angezeigt
- **buttonContainerClass** (string) *[Default: 'form-navigation‘]*
Class für die Button-Navigation
- **previousButtonClass** (string) *[Default: 'btn btn-cancel‘]*
Class für den Previous-Button
- **nextButtonClass** (string) *[Default: 'btn btn-primary‘]*
Class für den Next-Button
- **submitButtonClass** (string) *[Default: 'btn btn-primary‘]*
Class für den Submit-Button
- **renderLabel** (boolean) *[Default: TRUE]*

### Form-Elemente
- **containerClassAttribute** (string) *[Default: 'clearfix‘]*
Class für den Container um das Input-Feld- **errorClassAttribute** (string) *[Default: 'error']*

### SingleLineText
- **elementClassAttribute** (string) *[Default: '‘]*
Class für das Input-Feld
- **type** (string) *[Default: 'text‘]*
Type für das Input-Feld (text, email, number, etc.)
- **placeholder** (boolean) *[Default: FALSE]*
Wenn `TRUE` wird ein Placeholder ausgegeben

### MultiLineText
- **elementClassAttribute** (string) *[Default: '‘]*
Class für das Text-Feld
- **rows** (integer) *[Default: '‘]*
- **cols** (integer) *[Default: '‘]*
- **placeholder** (boolean) *[Default: FALSE]*
Wenn `TRUE` wird ein Placeholder ausgegeben

### Dotpulse.Form:Spinner
Fügt ein Nummer-Input hinzu

### Dotpulse.Form:Submit
Fügt einen Button an einem beliebigen Ort ein.
- **containerClassAttribute** (string) *[Default: 'clearfix‘]*
- **elementClassAttribute** (string) *[Default: 'btn btn-primary‘]*

### Dotpulse.Form:Column
Fügt ein Mehrspalten-Element ein
- **rowClassAttribute** (string) *[Default: 'row‘]*
Class für den Container
- **colClassAttribute** (string) *[Default: 'col-sm-6‘]*
- **colRenderPosition** (string) *[Default: FALSE]*
Für die erste Spalte verwenden sie das Attribut `first`. Für die letzte Spalte `last`.* * *
> ##### License Terms
> DE: Dieses Package wird durch webexcess unter der [GNU GPLv3 Lizenz](https://choosealicense.com/licenses/gpl-3.0/) verwaltet. Dieses Package und darin enthaltene oder hinzugefügte Quellcodes können exklusiv durch webexcess in Teilen oder als Ganzes zusätzlich und unter eigenem Namen unter der MIT-Lizenz veröffentlicht werden.

> EN: This package is managed by webexcess under the [GNU GPLv3 license](https://choosealicense.com/licenses/gpl-3.0/). This package and any sourcecode wich is included or added to it may be published exclusively by webexcess, in whole or in part, under its own name under the MIT license.

