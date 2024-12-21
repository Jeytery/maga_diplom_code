//
//  SpecialPointDataView.swift
//  maga-diplom
//
//  Created by Dmytro Ostapchenko on 20.11.2024.
//

import SwiftUI

struct SpecialPointDataView: View {
    @State private var pointType: String = "Select Type"
    @State private var pointName: String = "Select Name"
    @State private var importance: String = "Select Importance"
    @State private var pointTime: String = "Select Time"

    var body: some View {
        NavigationView {
            Form {

                Section {
                    HStack {
                        Text("Point Name")
                        Spacer()
                        Text(pointName)
                            .foregroundColor(.gray)
                    }
                    .contentShape(Rectangle())
                    .onTapGesture {
                        // Navigate or show picker for Point Name
                        print("Point Name tapped")
                    }
                }

                Section {
                    HStack {
                        Text("Point Time")
                        Spacer()
                        Text(pointTime)
                            .foregroundColor(.gray)
                    }
                    .contentShape(Rectangle())
                    .onTapGesture {
                        // Navigate or show picker for Point Time
                        print("Point Time tapped")
                    }
                }
                
                Section {
                    HStack {
                        Text("Point Position")
                        Spacer()
                        Text("Open map")
                            .foregroundColor(.gray)
                    }
                    .contentShape(Rectangle())
                    .onTapGesture {
                        print("Point Time tapped")
                    }
                }

                Section {
                    Button(action: {
                        // Add OK button logic here
                        print("OK button tapped")
                    }) {
                        HStack {
                            Spacer()
                            Text("OK")
                                .foregroundColor(.white)
                                .font(.system(size: 16, weight: .semibold))
                            Spacer()
                        }
                    }
                    .listRowBackground(Color.blue)
                }
            }
            .navigationTitle("Set point")
        }
    }
}


#Preview {
    SpecialPointDataView()
}
