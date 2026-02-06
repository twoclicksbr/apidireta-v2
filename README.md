# ApiDireta v2

![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)
![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)

## 📋 Sobre o Projeto

O **ApiDireta** é uma ferramenta SaaS de criação de API RESTful que permite aos usuários modelar a estrutura do banco de dados de forma visual (similar ao phpMyAdmin) e gera automaticamente os endpoints da API, sem necessidade de codificação.

Disponível em: [apidireta.com](https://apidireta.com)

## 🎯 Conceito Principal

**Analogia com phpMyAdmin:**
- **Projects** = Banco de dados
- **Modules** = Tabelas
- **Module_fields** = Campos/Colunas

O usuário cria sua estrutura de dados visualmente e o sistema gera automaticamente todos os endpoints CRUD necessários.

## ✨ Funcionalidades

### Multi-Tenancy
- Sistema multi-tenant completo
- Cada tenant possui planos (Go, Pro, Max)
- Isolamento total de dados entre tenants

### Bancos de Dados por Projeto
Cada projeto gera automaticamente 3 bancos de dados:
1. **Sandbox** (`apid_{tenant.slug}_{project.slug}_sand`) - Ambiente de desenvolvimento/testes
2. **Produção** (`apid_{tenant.slug}_{project.slug}_prod`) - Ambiente real
3. **Logs** (`apid_{tenant.slug}_{project.slug}_log`) - Sistema de logs

### Endpoints Automáticos
Cada módulo gera automaticamente 7 endpoints:
- **Index** (GET) - Listagem com paginação
- **Show** (GET) - Exibir registro específico
- **Store** (POST) - Criar novo registro
- **Update** (PUT/PATCH) - Atualizar registro
- **Soft Delete** (DELETE) - Exclusão lógica
- **Restore** (PATCH) - Restaurar registro excluído
- **Report** (GET) - Relatórios customizados

### Model Genérica Dinâmica
A aplicação utiliza uma model genérica que:
- Consulta `module_fields` antes de cada operação
- Monta dinamicamente `$fillable`, `$hidden`, `$casts`
- Configura relacionamentos automaticamente (belongsTo, hasMany)
- Aplica hash automático em campos `type=password`

### Sistema de Permissões
Controle granular em dois níveis:
1. **Permissões do Sistema** - Acesso aos módulos internos do ApiDireta
2. **Permissões de Endpoints** - Controle por endpoint dos módulos do cliente

## 🚀 Instalação

### Requisitos
- PHP 8.2 ou superior
- Composer
- MySQL 8.0 ou superior
- Node.js 18+ e NPM

### Passos

1. Clone o repositório:
```bash
git clone https://github.com/twoclicksbr/apidireta-v2.git
cd apidireta-v2
```

2. Instale as dependências:
```bash
composer install
npm install
```

3. Configure o ambiente:
```bash
cp .env.example .env
php artisan key:generate
```

4. Configure o banco de dados no `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=apidireta
DB_USERNAME=root
DB_PASSWORD=
```

5. Execute as migrations:
```bash
php artisan migrate
```

6. (Opcional) Execute os seeders:
```bash
php artisan db:seed
```

7. Compile os assets:
```bash
npm run dev
```

8. Inicie o servidor:
```bash
php artisan serve
```

## 📁 Estrutura do Banco de Dados

### Tabelas Principais

#### Gestão de Tenants
- `tenants` - Empresas cadastradas
- `plans` - Planos disponíveis (Go, Pro, Max)
- `plan_features` - Recursos de cada plano
- `tenant_plans` - Planos contratados pelos tenants

#### Usuários e Permissões
- `persons` - Dados pessoais dos usuários
- `users` - Dados de autenticação
- `person_permissions` - Permissões nos módulos do cliente
- `person_system_permissions` - Permissões nos módulos internos

#### Estrutura Dinâmica
- `projects` - Projetos de API (equivale a bancos de dados)
- `modules` - Módulos/Entidades (equivale a tabelas)
- `module_fields` - Campos dos módulos (equivale a colunas)
- `module_endpoints` - Documentação dos endpoints

## 🔐 Autenticação

O sistema utiliza tokens de acesso para controlar ambientes:
- `sand_token` - Token do ambiente Sandbox
- `prod_token` - Token do ambiente de Produção

## 📝 Regras de Negócio Importantes

1. **Campos Automáticos**: Na criação de cada módulo, 5 campos são gerados automaticamente:
   - `id` (ordem 1)
   - `order` (ordem 997)
   - `created_at` (ordem 998)
   - `updated_at` (ordem 999)
   - `deleted_at` (ordem 1000)

2. **Endpoints Automáticos**: 7 registros de `module_endpoints` são criados automaticamente

3. **Unicidade**: Controlada pela aplicação, não refletida no banco do tenant

4. **Relacionamentos**: Definidos via `related_module_id` e `related_module_field_id`

5. **Passwords**: Campos com `type=password` recebem hash (bcrypt) e são hidden automaticamente

6. **Timestamps**: Se `is_current_timestamp=true`, o `default_value` é ignorado

## 🛠️ Tecnologias

- **Backend**: Laravel 11.x
- **Database**: MySQL 8.0+
- **Frontend**: Blade Templates + Vite
- **Autenticação**: Laravel Sanctum/Passport
- **Testing**: Pest PHP

## 📖 Documentação Completa

Para documentação detalhada sobre a arquitetura, fluxos e modelagem completa das tabelas, consulte o arquivo [CLAUDE.md](./CLAUDE.md).

## 🤝 Contribuindo

Contribuições são bem-vindas! Por favor, siga estes passos:

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

## 📞 Contato

- Website: [apidireta.com](https://apidireta.com)
- GitHub: [@twoclicksbr](https://github.com/twoclicksbr)

## 🗺️ Roadmap

- [ ] Implementação da tabela `module_field_ui` para configurações de front-end
- [ ] Interface visual de criação de formulários
- [ ] Sistema de webhooks
- [ ] Importação/exportação de estruturas
- [ ] Templates de projetos pré-configurados

---

Desenvolvido com ❤️ usando Laravel
