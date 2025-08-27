<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\LandingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get("admin/login", [AdminController::class, "login"]);
Route::post("admin/login", [AdminController::class, "admin_login"])->name("admin.login");
Route::get("/{id}/application/{any?}", [HomeController::class, 'measurement'])->where('any', '.*')->name('project.measurement');
Route::get("/{id}/application", [HomeController::class, 'startMeasurement'])->name('project.start-measurement');
Route::prefix("admin")->group(function () {
    Route::get("/", [AdminController::class, "index"])->name("admin.index");

    Route::get('static/add', [AdminController::class, 'add_static'])->name('admin.static.add');
    Route::post('static/create', [AdminController::class, 'create_static'])->name('admin.static.create');
    Route::get('static/edit/{id}', [AdminController::class, 'edit_static'])->name('admin.static.edit');
    Route::get('static/delete/{id}', [AdminController::class, 'delete_static'])->name('admin.static.delete');
    Route::post('static/update/{id}', [AdminController::class, 'update_static'])->name('admin.static.update');

    Route::get("/equipments", [AdminController::class, "equipments"])->name("admin.equipments");
    Route::get('/equipments/add', [AdminController::class, 'add_equipments'])->name('admin.equipments.add');
    Route::post('/equipments/create', [AdminController::class, 'create_equipments'])->name('admin.equipments.create');
    Route::get("/equipments/delete/{id}", [AdminController::class, "delete_equipments"])->name("admin.equipments.delete");
    Route::get("/equipments/edit/{id}", [AdminController::class, "edit_equipments"])->name("admin.equipments.edit");
    Route::post("/equipments/update/{id}", [AdminController::class, "update_equipments"])->name("admin.equipments.update");


    Route::get("/labor", [AdminController::class, "labor"])->name("admin.labor");
    Route::get('/labor/add', [AdminController::class, 'add_labor'])->name("admin.labor.add");
    Route::post('/labor/create', [AdminController::class, 'create_labor'])->name('admin.labor.create');
    Route::get("/labor/delete/{id}", [AdminController::class, "delete_labor"])->name("admin.labor.delete");
    Route::get("/labor/edit/{id}", [AdminController::class, "edit_labor"])->name("admin.labor.edit");
    Route::post("/labor/update/{id}", [AdminController::class, "update_labor"])->name("admin.labor.update");

    Route::get('/users', [AdminController::class, 'user'])->name('admin.users');
    Route::get('/users/{id}', [AdminController::class, 'get_user'])->name('admin.users.get');
    Route::post('/user/update/{id}', [AdminController::class, 'update_user'])->name('admin.users.update');
    Route::post('/user/settings/update/{id}', [AdminController::class, 'update_user_settings'])->name('admin.users.setting');

    Route::get("/labor/class", [AdminController::class, "labor_class"])->name("admin.labor.class");
    Route::post("/labor/class/create", [AdminController::class, "create_labor_class"])->name("admin.labor.class.create");
    Route::post("/labor/class/update/{id}", [AdminController::class, "update_labor_class"])->name("admin.labor.class.update");
    Route::get("/labor/class/delete/{id}", [AdminController::class, "delete_labor_class"])->name("admin.labor.class.delete");

    Route::get("/labor/type", [AdminController::class, "labor_type"])->name("admin.labor.type");
    Route::post("/labor/type/create", [AdminController::class, "create_labor_type"])->name("admin.labor.type.create");
    Route::get("/labor/type/delete/{id}", [AdminController::class, "delete_labor_type"])->name("admin.labor.type.delete");

    Route::get("/crews", [AdminController::class, "crews"])->name("admin.crews");
    Route::get("/crews/delete/{id}", [AdminController::class, "delete_crews"])->name("admin.crews.delete");
    Route::get("/crews/edit/{id}", [AdminController::class, "edit_crews"])->name("admin.crews.edit");
    Route::post("/crews/update/{id}", [AdminController::class, "update_crews"])->name("admin.crews.update");

    Route::get("/contacts", [AdminController::class, "contacts"])->name("admin.contacts");
    Route::get("/contacts/delete/{id}", [AdminController::class, "delete_contacts"])->name("admin.contacts.delete");
    Route::get("/contacts/edit/{id}", [AdminController::class, "edit_contacts"])->name("admin.contacts.edit");
    Route::post("/contacts/update/{id}", [AdminController::class, "update_contacts"])->name("admin.contacts.update");

    Route::get("/company", [AdminController::class, "company"])->name("admin.company");
    Route::get("/company/delete/{id}", [AdminController::class, "delete_company"])->name("admin.company.delete");
    Route::get("/company/edit/{id}", [AdminController::class, "edit_company"])->name("admin.company.edit");
    Route::post("/company/update/{id}", [AdminController::class, "update_company"])->name("admin.company.update");

    Route::get("/opening", [AdminController::class, "opening"])->name("admin.opening");
    Route::get("/opening/delete/{id}", [AdminController::class, "delete_opening"])->name("admin.opening.delete");
    Route::get("/opening/edit/{id}", [AdminController::class, "edit_opening"])->name("admin.opening.edit");
    Route::post("/opening/update/{id}", [AdminController::class, "update_opening"])->name("admin.opening.update");

    Route::get('opening/shapes', [AdminController::class, 'opening_shape'])->name('admin.opening.shape');
    Route::post('opening/shapes/create', [AdminController::class, 'create_opening_shape'])->name('admin.opening.shape.create');
    Route::get('opening/shapes/delete/{id}', [AdminController::class, 'delete_opening_shape'])->name('admin.opening.shape.delete');

    Route::get("/material/division", [AdminController::class, "material_division"])->name("admin.material.division");
    Route::post("/material/division/create", [AdminController::class, "material_division_create"])->name("admin.material.division.create");
    Route::post("/material/division/update/{id}", [AdminController::class, "material_division_update"])->name("admin.material.division.update");
    Route::get("/material/division/delete/{id}", [AdminController::class, "material_division_delete"])->name("admin.material.division.delete");
    Route::get('material/division/class/{id?}', [AdminController::class, 'get_material_class'])->name('admin.material_class');
    Route::get("/material/class", [AdminController::class, "material_class"])->name("admin.material.class");
    Route::post("/material/class/create", [AdminController::class, "material_class_create"])->name("admin.material.class.create");
    Route::post("/material/class/update/{id}", [AdminController::class, "material_class_update"])->name("admin.material.class.update");
    Route::get("/material/class/delete/{id}", [AdminController::class, "material_class_delete"])->name("admin.material.class.delete");

    Route::get("/material", [AdminController::class, "material"])->name("admin.material");
    Route::get('/material/add', [AdminController::class, 'add_material'])->name('admin.material.add');
    Route::post('/material/create', [AdminController::class, 'create_material'])->name('admin.material.create');
    Route::get("/material/delete/{id}", [AdminController::class, "delete_material"])->name("admin.material.delete");
    Route::get("/material/edit/{id}", [AdminController::class, "edit_material"])->name("admin.material.edit");
    Route::post("/material/update/{id}", [AdminController::class, "update_material"])->name("admin.material.update");

    Route::get("/material/unit", [AdminController::class, 'default_units'])->name('admin.material.units');
    Route::get("/material/unit/delete/{id}", [AdminController::class, 'delete_default_unit'])->name('admin.material.unit.delete');
    Route::post("/material/unit/update/{id}", [AdminController::class, 'update_default_unit'])->name('admin.material.unit.update');
    Route::post("/material/unit/create", [AdminController::class, 'create_default_unit'])->name('admin.material.unit.create');
    Route::get("/logout", [AdminController::class, "logout"])->name("admin.logout");

    Route::post('settings/update', [AdminController::class, 'setting_update'])->name('admin.settings.update');

    Route::get('security/password', [AdminController::class, 'change'])->name('admin.security.change');
    Route::post('security/password', [AdminController::class, 'change_password'])->name('admin.security.change');

    Route::get('subscriptions', [AdminController::class, 'subscriptions'])->name('admin.subscriptions');
    Route::post('subscriptions', [AdminController::class, 'create_subscriptions'])->name('admin.subscriptions');
    Route::get('subscriptions/delete/{id}', [AdminController::class, 'delete_subscriptions'])->name('admin.subscriptions.delete');
    Route::get('subscriptions/edit/{id}', [AdminController::class, 'edit_subscriptions'])->name('admin.subscriptions.edit');
    Route::post('subscriptions/update/{id}', [AdminController::class, 'update_subscriptions'])->name('admin.subscriptions.update');
});

Route::get("/login", [AuthController::class, "login"])->name("login");
Route::post("/login", [AuthController::class, "do_login"])->name('do_login');

Route::get("/register", [AuthController::class, "register"])->name('register');
Route::post("/register", [AuthController::class, "do_register"])->name('do_register');

Route::get('/subsription', [AuthController::class, 'purchase_subscription'])->name('subscription.purchase');
Route::get('/subsription/intent/{id?}', [AuthController::class, 'create_intent'])->name('subscription.intent');
Route::get('/subsription/confirm', [AuthController::class, 'confirm_subscription'])->name('subscription.confirm');
Route::get('/success/change-password', [AuthController::class, 'passwordResetConfirm'])->name('password.success');
Route::get('/reset-password', [AuthController::class, 'passwordReset'])->name('password.reset');
Route::get('/change-password/{token}', [AuthController::class, 'changePassword'])->name('password.changePassword');
Route::post('/change-password/{token}', [AuthController::class, 'doChangePassword'])->name('password.changePassword');
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/page/{slug}', [LandingController::class, 'static_page'])->name('landing.static');
Route::prefix("/")->middleware('auth', 'location')->group(function () {
    Route::get('/add-number', [AuthController::class, 'add_number'])->name('add_number');
    Route::post('/add-number', [AuthController::class, 'do_add_number'])->name('do_add_number');
    Route::get('/labor', [HomeController::class, 'labor'])->name('labor');
    Route::get('/labor/create', [HomeController::class, 'create_labor_page'])->name('labor.create');
    Route::post('/labor/create', [HomeController::class, 'create_labor'])->name('labor.create');
    Route::get('/labor/import/{id}', [HomeController::class, 'import_labor'])->name('labor.import');
    Route::get('/labor/delete/{id}', [HomeController::class, 'delete_labor'])->name('labor.delete');
    Route::get('/labor/edit/{id}', [HomeController::class, 'edit_labor'])->name('labor.edit');
    Route::post('/labor/update/{id}', [HomeController::class, 'update_labor'])->name('labor.update');

    Route::get('/crew', [HomeController::class, 'crew'])->name('crew');
    Route::get('/crew/create', [HomeController::class, 'create_crew_page'])->name('crew.create');
    Route::post('/crew/create', [HomeController::class, 'create_crew'])->name('crew.create');
    Route::get('/crew/delete/{id}', [HomeController::class, 'delete_crew'])->name('crew.delete');
    Route::get('/crew/edit/{id}', [HomeController::class, 'edit_crew'])->name('crew.edit');
    Route::post('/crew/update/{id}', [HomeController::class, 'update_crew'])->name('crew.update');

    Route::get('/equipment', [HomeController::class, 'equipment'])->name('equipment');
    Route::get('/equipment/create', [HomeController::class, 'create_equipment_page'])->name('equipment.create');
    Route::post('/equipment/create', [HomeController::class, 'create_equipment'])->name('equipment.create');
    Route::get('/equipment/import/{id}', [HomeController::class, 'import_equipment'])->name('equipment.import');
    Route::get('/equipment/delete/{id}', [HomeController::class, 'delete_equipment'])->name('equipment.delete');
    Route::get('/equipment/edit/{id}', [HomeController::class, 'edit_equipment'])->name('equipment.edit');
    Route::post('/equipment/update/{id}', [HomeController::class, 'update_equipment'])->name('equipment.update');

    Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
    Route::get('/contact/create', [HomeController::class, 'create_contact_page'])->name('contact.create');
    Route::post('/contact/create', [HomeController::class, 'create_contact'])->name('contact.create');
    Route::get('/contact/delete/{id}', [HomeController::class, 'delete_contact'])->name('contact.delete');
    Route::get('/contact/edit/{id}', [HomeController::class, 'edit_contact'])->name('contact.edit');
    Route::post('/contact/update/{id}', [HomeController::class, 'update_contact'])->name('contact.update');

    Route::get('/company', [HomeController::class, 'company'])->name('company');
    Route::post('/company', [HomeController::class, 'create_company'])->name('company.create');
    Route::get('/company/delete/{id}', [HomeController::class, 'delete_company'])->name('company.delete');
    Route::get('/company/edit/{id}', [HomeController::class, 'edit_company'])->name('company.edit');
    Route::post('/company/update/{id}', [HomeController::class, 'update_company'])->name('company.update');

    Route::get('/opening', [HomeController::class, 'opening'])->name('opening');
    Route::get('/opening/create', [HomeController::class, 'create_opening_page'])->name('opening.create');
    Route::post('/opening/create', [HomeController::class, 'create_opening'])->name('opening.create');
    Route::get('/opening/delete/{id}', [HomeController::class, 'delete_opening'])->name('opening.delete');
    Route::get('/opening/edit/{id}', [HomeController::class, 'edit_opening'])->name('opening.edit');
    Route::post('/opening/update/{id}', [HomeController::class, 'update_opening'])->name('opening.update');

    Route::get('/material', [HomeController::class, 'material'])->name('material');
    Route::post('/material', [HomeController::class, 'create_material'])->name('material.create');
    Route::get('/material/add', [HomeController::class, 'add_material'])->name('material.add');
    Route::get('/material/import/{id}', [HomeController::class, 'import_material'])->name('material.import');
    Route::get('/material/delete/{id}', [HomeController::class, 'delete_material'])->name('material.delete');
    Route::get('/material/edit/{id}', [HomeController::class, 'edit_material'])->name('material.edit');
    Route::post('/material/update/{id}', [HomeController::class, 'update_material'])->name('material.update');
    Route::get('material/division/class/{id?}', [HomeController::class, 'get_material_class'])->name('material_class');

    Route::get('/project', [HomeController::class, 'project'])->name('project');
    Route::get('/project/create', [HomeController::class, 'create_project_page'])->name('project.create');
    Route::post('/project/create', [HomeController::class, 'create_project'])->name('project.create');
    Route::get('/project/delete/{id}', [HomeController::class, 'delete_project'])->name('project.delete');
    Route::get('/project/edit/{id}', [HomeController::class, 'edit_project'])->name('project.edit');
    Route::post('/project/update/{id}', [HomeController::class, 'update_project'])->name('project.update');
    Route::post('/project/create-new-project', [HomeController::class, 'createNewProject'])->name('project.create-new-project');
    Route::get('/project/edit-new-project/{id}', [HomeController::class, 'editNewProject'])->name('project.edit-project');
    Route::post('/project/update-project', [HomeController::class, 'updateNewProject'])->name('project.update-project');

    Route::get('assembly', [HomeController::class, 'assembly'])->name('assembly');

    Route::post('settings/update', [HomeController::class, 'update_settings'])->name('settings.update');

    Route::get('notification/status', [ApiController::class, 'notification_status'])->name('notification.status');
    Route::get('logout', [AuthController::class, 'logout'])->name('logout');
   //Invite User routes
    Route::post('/invite', [InviteController::class, 'invite'])->name('invite');
    Route::get('/invite-user', [InviteController::class, 'inviteUser'])->name('invite-user');
    Route::post("/invite-login", [InviteController::class, "inviteLogin"])->name('invite-login');
    Route::post("/invite-register", [InviteController::class, "inviteRegister"])->name('invite-register');
    Route::get('/invite-project-user/{id}', [InviteController::class, 'inviteProjectUser'])->name('invite-project-user');
    Route::get('/delete-invite-user/{id}', [InviteController::class, 'deleteInviteUser'])->name('delete-invite-user');
});