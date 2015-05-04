<?php


$pdo = new \PDO("mysql:host=localhost;dbname=ormtest" ,"root", "");

$mapper = new \MyApp\Mapper($pdo);

// Creation
$user = $mapper->create(User::TABLE, [
	User::roleId    => 1,
	User::firstName => "Chair",
	User::lastName  => "Face",
	User::email     => "chairface@notadomain.gov",
	User::password  => "notmypassword"
]);

// Read One
$bestUser = $mapper
	->from(User::TABLE)
	->join(Role::TABLE, [Role::roleId => User::roleId])
	->where([Role::name => "Best"])
	->readOne();

// Read all
$allUsers = $mapper
	->from(User::TABLE)
	->where([User::firstName => "Chair"])
	->readAll();

// Read paginated
$pageUsers = $mapper
	->from(User::TABLE)
	->orderAsc(User::lastName)
	->readSlice(1, 20);



// Update many
$mapper->from(User::TABLE)
	->where([User::userId => $user->getUserId()])
	->update([
		User::firstName => "Jeff",
		User::lastName  => "JefftyJeff"
	]);

// Delete many
$mapper->from(User::TABLE)
	->join(Role::TABLE, [ Role::roleId => User::roleId ])
	->where([User::userId => $bestUser->getUserId()])
	->delete();



// Update single
$user->setFirstName("Jeff")
	->setLastName("JefftyJeff")
	->save();

// Delete single
$user->delete();