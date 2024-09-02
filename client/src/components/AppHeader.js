import React from 'react'
import './../assets/css/custom.css'
import { CContainer, CHeader, CHeaderNav, CNavLink } from '@coreui/react'
import { AppHeaderDropdown } from './header/index'

const AppHeader = () => {
  return (
    <CHeader position="sticky" className="mb-4">
      <CContainer fluid>
        <CHeaderNav className="me-auto" id="async-search" style={{ position: 'relative' }}>
          <CNavLink href="#" active>
            Employee Logs
          </CNavLink>
        </CHeaderNav>

        <CHeaderNav className="ms-3">
          <AppHeaderDropdown />
        </CHeaderNav>
      </CContainer>
    </CHeader>
  )
}

export default AppHeader
