const routeMap = {
  about: '/administration/about',
  apiTokens: '/administration/api-token',
  authentication: '/administration/authentication',
  authenticationDenied: '/authentication-denied',
  cloudNotificationConfiguration: '/configuration/notifications',
  dashboard: '/home/dashboards/:layout/:dashboardId',
  dashboards: '/home/dashboards/:layout',
  extensionsManagerPage: '/administration/extensions/manager',
  install: '/install/install.php',
  login: '/login',
  logout: '/logout',
  notAllowedPage: '/not-allowed',
  pollerList: '/main.php?p=60901',
  pollerWizard: '/poller-wizard/1',
  resetPassword: '/reset-password',
  resourceAccessManagement: '/administration/resource-access/rules',
  resources: '/monitoring/resources',
  upgrade: '/install/upgrade.php'
};

export default routeMap;
