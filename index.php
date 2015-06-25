<?php
session_start();

// Get the configuration variable
$config = parse_ini_file('config.ini', true);

// Include the PHP class file
include_once ('class.afasappconnector.php');
$AFASappconnector = new AFASappconnector($config['AFASappconnector']);

if (isset($_REQUEST['method'])) {
	if ($_REQUEST['method'] == 'reset') {
		session_destroy();
		session_start();
	} elseif ($_REQUEST['method'] == 'GenerateOTP') {
		$_SESSION['userId'] = $_REQUEST['userId'];
		$response = $AFASappconnector->GenerateOTP($_REQUEST['userId']);
	} elseif ($_REQUEST['method'] == 'GetTokenFromOTP') {
		$response = $AFASappconnector->GetTokenFromOTP($_REQUEST['userId'], $_REQUEST['otp']);
		if (isset($response->GetTokenFromOTPResult)) {
			$token = simplexml_load_string($response->GetTokenFromOTPResult);
			if (isset($token->data)) {
				$_SESSION['token'] = (string) $token->data;
			}
		}
	} elseif ($_REQUEST['method'] == 'GetData') {
		$response = $AFASappconnector->GetData($_REQUEST['token'], $_REQUEST['connectorId'], $_REQUEST['filtersXml'], $_REQUEST['skip'], $_REQUEST['take']);
	} elseif ($_REQUEST['method'] == 'Execute') {
		$response = $AFASappconnector->Execute($_REQUEST['token'], $_REQUEST['connectorType'], $_REQUEST['dataXml'], $_REQUEST['connectorVersion']);
	} elseif ($_REQUEST['method'] == 'DeleteToken') {
		$response = $AFASappconnector->DeleteToken($_REQUEST['token']);
		session_destroy();
		session_start();
	}
}

?><!DOCTYPE html>
<html lang="nl">

<head>

<title>AFAS AppConnector</title>
<meta name="robots" content="noindex, nofollow" />
<link type="image/x-icon" href="favicon.ico" rel="icon" />
<link type="image/x-icon" href="favicon.ico" rel="shortcut icon" />
<meta name="viewport" content="width=device-width, initial-scale=1" />

<link
	href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css"
	rel="stylesheet" />

</head>

<body>

<div class="container">
	<h1><a href="<?php echo $_SERVER['PHP_SELF']?>">AFAS AppConnector</a></h1>
	<p class="lead">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse felis.</p>

	<div class="row">
		<div class="col-md-6">
<?php if (!isset($_SESSION['userId'])):?>
			<form method="post" action="<?php echo $_SERVER['PHP_SELF']?>">
				<div class="panel panel-info">
					<div class="panel-heading">
						<h2 class="panel-title">GenerateOTP</h2>
					</div>
					<div class="panel-body">
						<div class="form-group">
							<label for="userId">Username</label>
							<input type="text" class="form-control" id="userId" name="userId" placeholder="userId" autofocus />
						</div>
						<input type="hidden" name="method" value="GenerateOTP" />
					</div>
					<div class="panel-footer text-right">
						<input type="submit" class="btn btn-primary" name="submit" value="Submit" />
					</div>
				</div>
			</form>
<?php elseif (isset($_SESSION['userId']) && !isset($_SESSION['token'])):?>
			<form method="post" action="<?php echo $_SERVER['PHP_SELF']?>">
				<div class="panel panel-info">
					<div class="panel-heading">
						<h2 class="panel-title">GetTokenFromOTP</h2>
					</div>
					<div class="panel-body">
						<div class="form-group">
							<label for="userId">Username</label>
							<input type="text" class="form-control" id="userId" name="userId" placeholder="userId" value="<?php echo $_SESSION['userId']?>" />
						</div>
						<div class="form-group">
							<label for="otp">One Time Password</label>
							<input type="number" class="form-control" name="otp" id="otp" placeholder="otp" autofocus />
						</div>
						<input type="hidden" name="method" value="GetTokenFromOTP" />
					</div>
					<div class="panel-footer text-right">
						<input type="submit" class="btn btn-primary" name="submit" value="Submit" />
					</div>
				</div>
			</form>
<?php elseif (isset($_SESSION['userId']) && isset($_SESSION['token'])):?>
			<form method="post" action="<?php echo $_SERVER['PHP_SELF']?>">
				<div class="panel panel-info">
					<div class="panel-heading">
						<h2 class="panel-title">GetData</h2>
					</div>
					<div class="panel-body">
						<div class="form-group">
							<label for="token">Token</label>
							<input type="text" class="form-control" id="token" name="token" placeholder="token" value="<?php echo $_SESSION['token']?>" />
						</div>
						<div class="form-group">
							<label for="connectorId">GetConnector</label>
							<input type="text" class="form-control" name="connectorId" id="connectorId" placeholder="connectorId" value="<?php echo (isset($_REQUEST['connectorId'])) ? $_REQUEST['connectorId'] : 'ProfitCountries'?>" />
						</div>
						<div class="form-group">
							<label for="filtersXml">Filter</label>
							<textarea class="form-control" id="filtersXml" name="filtersXml" placeholder="filtersXml"><?php echo (isset($_REQUEST['filtersXml'])) ? $_REQUEST['filtersXml'] : ''?></textarea>
						</div>
						<div class="row">
							<div class="col-xs-6">
								<div class="form-group">
									<label for="skip">Skip</label>
									<input type="number" class="form-control" name="skip" id="skip" placeholder="skip" value="<?php echo (isset($_REQUEST['skip'])) ? $_REQUEST['skip'] : 0?>" />
								</div>
							</div>
							<div class="col-xs-6">
								<div class="form-group">
									<label for="take">Take</label>
									<input type="number" class="form-control" name="take" id="take" placeholder="take" value="<?php echo (isset($_REQUEST['take'])) ? $_REQUEST['take'] : 10?>" />
								</div>
							</div>
						</div>
						<input type="hidden" name="method" value="GetData" />
					</div>
					<div class="panel-footer text-right">
						<input type="submit" class="btn btn-primary" name="submit" value="Submit" />
					</div>
				</div>
			</form>
			<form method="post" action="<?php echo $_SERVER['PHP_SELF']?>">
				<div class="panel panel-info">
					<div class="panel-heading">
						<h2 class="panel-title">Execute</h2>
					</div>
					<div class="panel-body">
						<div class="form-group">
							<label for="token">Token</label>
							<input type="text" class="form-control" id="token" name="token" placeholder="token" value="<?php echo $_SESSION['token']?>" />
						</div>
						<div class="row">
							<div class="col-xs-9">
								<div class="form-group">
									<label for="connectorType">UpdateConnector</label>
									<input type="text" class="form-control" name="connectorType" id="connectorType" placeholder="connectorType" value="<?php echo (isset($_REQUEST['connectorType'])) ? $_REQUEST['connectorType'] : 'KnPerson'?>" />
								</div>
							</div>
							<div class="col-xs-3">
								<div class="form-group">
									<label for="connectorVersion">Version</label>
									<input type="number" class="form-control" name="connectorVersion" id="connectorVersion" placeholder="connectorVersion" value="<?php echo (isset($_REQUEST['connectorVersion'])) ? $_REQUEST['connectorVersion'] : 1?>" />
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="dataXml">XML data</label>
							<textarea class="form-control" id="dataXml" name="dataXml" placeholder="dataXml"><?php echo (isset($_REQUEST['dataXml'])) ? $_REQUEST['dataXml'] : ''?></textarea>
						</div>
						<input type="hidden" name="method" value="Execute" />
					</div>
					<div class="panel-footer text-right">
						<input type="submit" class="btn btn-primary" name="submit" value="Submit" />
					</div>
				</div>
			</form>
			<form method="post" action="<?php echo $_SERVER['PHP_SELF']?>">
				<div class="panel panel-info">
					<div class="panel-heading">
						<h2 class="panel-title">DeleteToken</h2>
					</div>
					<div class="panel-body">
						<div class="form-group">
							<label for="token">Token</label>
							<input type="text" class="form-control" id="token" name="token" placeholder="token" value="<?php echo $_SESSION['token']?>" />
						</div>
						<input type="hidden" name="method" value="DeleteToken" />
					</div>
					<div class="panel-footer text-right">
						<input type="submit" class="btn btn-primary" name="submit" value="Submit" />
					</div>
				</div>
			</form>
			<?php endif?>
		<a class="btn btn-block btn-default" href="?method=reset">Reset</a>
		</div>
		
		
		
		<div class="col-md-6">
			<div class="panel panel-info">
				<div class="panel-heading" role="tab">
					<h2 class="panel-title">
						<a role="button" data-toggle="collapse" href="#collapseSession" role="tabpanel">Session</a>
					</h2>
				</div>
				<div class="panel-collapse collapse in" id="collapseSession">
					<div class="panel-body">
						<pre><?php var_dump($_SESSION)?></pre>
					</div>
				</div>
			</div>
<?php if (is_array($_REQUEST) && count($_REQUEST) > 0):?>
			<div class="panel panel-info">
				<div class="panel-heading" role="tab">
					<h2 class="panel-title">
						<a role="button" data-toggle="collapse" href="#collapseRequest" role="tabpanel">Request</a>
					</h2>
				</div>
				<div class="panel-collapse collapse in" id="collapseRequest">
					<div class="panel-body">
						<pre><?php var_dump($_REQUEST)?></pre>
					</div>
				</div>
			</div>
<?php endif?>
<?php if (isset($response)):?>
			<div class="panel panel-info">
				<div class="panel-heading" role="tab">
					<h2 class="panel-title">
						<a role="button" data-toggle="collapse" href="#collapseResponse" role="tabpanel">Response</a>
					</h2>
				</div>
				<div class="panel-collapse collapse in" id="collapseResponse">
					<div class="panel-body">
						<pre><?php var_dump($response)?></pre>
					</div>
				</div>
			</div>
<?php endif?>
			<div class="panel panel-info">
				<div class="panel-heading" role="tab">
					<h2 class="panel-title">
						<a role="button" data-toggle="collapse" href="#collapseAppConnector" role="tabpanel">AppConnector</a>
					</h2>
				</div>
				<div class="panel-collapse collapse out" id="collapseAppConnector">
					<div class="panel-body">
						<pre><?php var_dump($AFASappconnector)?></pre>
					</div>
				</div>
			</div>
			<div class="panel panel-info">
				<div class="panel-heading" role="tab">
					<h2 class="panel-title">
						<a role="button" data-toggle="collapse" href="#collapseServer" role="tabpanel">Server</a>
					</h2>
				</div>
				<div class="panel-collapse collapse out" id="collapseServer">
					<div class="panel-body">
						<pre><?php var_dump($_SERVER)?></pre>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="//code.jquery.com/jquery-2.1.4.min.js"></script>
<script	src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->

</body>

</html>