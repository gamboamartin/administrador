# administrador
## base
### orm
#### where
##### Función: `where_mayus`

**Descripción:** Esta función verifica si una declaración WHERE no está vacía, la convierte a mayúsculas y 
devuelve un error si la declaración WHERE no se usa correctamente.

**Parámetros:**
- `$complemento`: Este es un objeto `stdClass` de entrada, que incluye la 
- declaración 'where' a verificar.

**Devuelve:**
Un array o un objeto `stdClass`. Si la declaración WHERE no está vacía y no 
es solo 'WHERE', devuelve un error con la declaración WHERE incorrectamente 
aplicada. Si no, devuelve el objeto `$complemento` modificado.

**Excepciones:**
Lanza una excepción si la cláusula WHERE no es correcta.

**Ejemplo de uso:**
1. Entrada: `$complemento->where = "where column = value";` retorna "Error where mal aplicado"
2. Entrada: `$complemento->where = "";` retorna el objeto `$complemento` sin cambios en la propiedad where.
3. Entrada: `$complemento->where = "WHERE";` retorna el objeto `$complemento` sin cambios en la propiedad where.