import React from 'react'

const Dashboard = React.lazy(() => import('./views/dashboard/Dashboard'))
const Profile = React.lazy(() => import('./views/profile/Profile'))

const routes = [
  {
    path: '/dashboard',
    name: 'Dashboard',
    element: Dashboard,
  },

  {
    path: '/profile',
    name: 'Profile',
    element: Profile,
  },
]

export default routes
