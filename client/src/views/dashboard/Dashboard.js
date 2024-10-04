import React, { useEffect } from 'react'
import {
  CButton,
  CCard,
  CCardBody,
  CCol,
  CForm,
  CFormInput,
  CRow,
  CSpinner,
  CTable,
  CTableBody,
  CTableDataCell,
  CTableHead,
  CTableHeaderCell,
  CTableRow,
} from '@coreui/react'
import { useFormik } from 'formik'
import { ToastContainer } from 'react-toastify'
import { jwtDecode } from 'jwt-decode'
import 'animate.css'
import { api } from 'src/components/SystemConfiguration'
import { Skeleton } from '@mui/material'
import 'intro.js/introjs.css'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { fa1, faSearch } from '@fortawesome/free-solid-svg-icons'

const Dashboard = () => {
  const queryClient = useQueryClient()
  const user = jwtDecode(localStorage.getItem('employeeLogsToken'))

  const form = useFormik({
    initialValues: {
      date: '',
    },
    onSubmit: async (values) => {
      await filterAttendance.mutate(values)
    },
  })

  const filterAttendance = useMutation({
    mutationFn: async (values) => {
      return api.get('attendance/get_employee_logs', {
        params: {
          date: values.date,
          employee_id: user.employee_id,
          logsched: user.logsched,
        },
      })
    },
    onSuccess: async (responses) => {
      await queryClient.setQueryData(['attendance', user.employee_id], responses.data)
    },
    onError: (error) => {
      console.info(error.response.data)
      // toast.error(error.response.data.message)
    },
  })

  let defaultMonth = ''
  useEffect(() => {
    const currentDate = new Date()
    const year = currentDate.getFullYear()
    const month = String(currentDate.getMonth() + 1).padStart(2, '0')
    defaultMonth = `${year}-${month}`
    form.setFieldValue('date', defaultMonth)
  }, [])
  const attendance = useQuery({
    queryFn: async () => {
      const response = await api.get('attendance/get_employee_logs', {
        params: {
          date: defaultMonth,
          employee_id: user.employee_id,
        },
      })
      return response.data
    },
    queryKey: ['attendance', user.employee_id],
    staleTime: Infinity,
    // refetchInterval: 1000,
  })
  const formatDate = (dateStr) => {
    const date = new Date(dateStr)
    const options = { month: 'long', year: 'numeric' }
    return date.toLocaleDateString('en-US', options)
  }

  const date_from = new Date(user.date_from)
  const date_to = new Date(user.date_to)

  const options = { year: 'numeric', month: 'short', day: 'numeric' }
  const formattedDateFrom = date_from.toLocaleDateString('en-US', options)
  const formattedDateTo = date_to.toLocaleDateString('en-US', options)
  return (
    <>
      <ToastContainer />
      <CCard className="mb-4">
        <CCardBody>
          <CRow className="mb-3">
            <CCol md={12}>
              <h5>
                <lord-icon
                  src="https://cdn.lordicon.com/mebvgwrs.json"
                  trigger="in"
                  style={{
                    width: '50px',
                    height: '50px',
                    marginBottom: '-10px',
                    marginRight: '-5px',
                  }}
                ></lord-icon>{' '}
                Welcome {user.first_name} {user.middle_name} {user.last_name},
              </h5>
              <CRow className="justify-content-between">
                <CCol xs={4}>
                  <table style={{ fontSize: 14 }}>
                    <tr>
                      <td>Employee ID</td>
                      <td>:</td>
                      <td>
                        <strong> {user.employee_id} </strong>
                      </td>
                    </tr>
                    <tr>
                      <td>Latest Contract</td>
                      <td>:</td>
                      <td>
                        <strong>
                          {formattedDateFrom} - {formattedDateTo}
                        </strong>
                      </td>
                    </tr>
                  </table>
                </CCol>
                <CCol xs={4}>
                  <table style={{ fontSize: 14 }}>
                    <tr>
                      <td>Log Schedule</td>
                      <td>:</td>
                      <td>
                        <strong> {user.logsched} </strong>
                      </td>
                    </tr>
                    <tr>
                      <td>Office Assigned</td>
                      <td>:</td>
                      <td>
                        <strong> {user.office_assigned} </strong>
                      </td>
                    </tr>
                  </table>
                </CCol>
              </CRow>
            </CCol>
          </CRow>
        </CCardBody>
      </CCard>

      <CRow>
        <CCol md={4}>
          <CCard>
            <CCardBody>
              <CForm onSubmit={form.handleSubmit}>
                <CFormInput
                  label="Date"
                  onChange={form.handleChange}
                  type="month"
                  value={form.values.date}
                  name="date"
                />
                <div className="d-grid gap-2 end mt-3">
                  <CButton type="submit" color="primary">
                    <FontAwesomeIcon icon={faSearch} /> Search
                  </CButton>
                </div>
              </CForm>
            </CCardBody>
          </CCard>
        </CCol>
        <CCol md={8}>
          <CCard style={{ position: 'relative' }}>
            <CCardBody>
              {attendance.isLoading || filterAttendance.isPending ? (
                <>
                  <Skeleton variant="text" height={30} width={300} />
                </>
              ) : (
                <h5>For the Month of {formatDate(form.values.date)}</h5>
              )}
            </CCardBody>
            <CTable responsive bordered small>
              <CTableHead>
                <CTableRow className="text-center">
                  <CTableHeaderCell scope="col" colSpan={2}>
                    Date
                  </CTableHeaderCell>
                  <CTableHeaderCell scope="col">Time In</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Time Out</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Time In</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Time Out</CTableHeaderCell>
                </CTableRow>
              </CTableHead>
              <CTableBody>
                {attendance.isLoading || filterAttendance.isPending
                  ? [...Array(20)].map((_, index) => {
                      return (
                        <CTableRow key={index}>
                          {[...Array(5)].map((_, rowIndex) => (
                            <CTableDataCell key={rowIndex}>
                              <Skeleton
                                variant="text"
                                height={20}
                                style={{ margin: '0 auto' }}
                                width={rowIndex === 0 ? '100%' : '40%'}
                              />
                            </CTableDataCell>
                          ))}
                        </CTableRow>
                      )
                    })
                  : attendance?.data?.map((row, index) => {
                      return (
                        <CTableRow key={index}>
                          <CTableDataCell
                            scope="row"
                            style={
                              row.date === 'Saturday' || row.date === 'Sunday'
                                ? { backgroundColor: '#F5F5F5' }
                                : {}
                            }
                          >
                            {row.day}
                          </CTableDataCell>
                          <CTableDataCell
                            scope="row"
                            style={
                              row.date === 'Saturday' || row.date === 'Sunday'
                                ? { backgroundColor: '#F5F5F5' }
                                : {}
                            }
                          >
                            {row.date}
                          </CTableDataCell>
                          <CTableDataCell
                            style={
                              row.date === 'Saturday' || row.date === 'Sunday'
                                ? { backgroundColor: '#F5F5F5' }
                                : {}
                            }
                            className="text-center"
                          >
                            {row?.login1}
                          </CTableDataCell>
                          <CTableDataCell
                            style={
                              row.date === 'Saturday' || row.date === 'Sunday'
                                ? { backgroundColor: '#F5F5F5' }
                                : {}
                            }
                            className="text-center"
                          >
                            {row?.logout1}
                          </CTableDataCell>
                          <CTableDataCell
                            style={
                              row.date === 'Saturday' || row.date === 'Sunday'
                                ? { backgroundColor: '#F5F5F5' }
                                : {}
                            }
                            className="text-center"
                          >
                            {row?.login2}
                          </CTableDataCell>
                          <CTableDataCell
                            style={
                              row.date === 'Saturday' || row.date === 'Sunday'
                                ? { backgroundColor: '#F5F5F5' }
                                : {}
                            }
                            className="text-center"
                          >
                            {row?.logout2}
                          </CTableDataCell>
                        </CTableRow>
                      )
                    })}
              </CTableBody>
            </CTable>
          </CCard>
        </CCol>
      </CRow>
    </>
  )
}

export default Dashboard
